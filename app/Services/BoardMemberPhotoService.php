<?php

namespace App\Services;

use App\Models\BoardMember;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class BoardMemberPhotoService
{
    public const MAIN_WIDTH = 800;

    public const MAIN_HEIGHT = 1000; // 4:5

    public const THUMB_WIDTH = 400;

    public const THUMB_HEIGHT = 500; // 4:5

    public const MIN_SIDE = 400; // Sisi pendek minimal 400px

    public const MAX_SIZE_BYTES = 5242880; // 5 MB

    /**
     * Proses berkas foto pengurus:
     * 1. Validasi berkas gambar sungguhan (MIME & GD decoding).
     * 2. Validasi dimensi minimal (sisi terpendek >= 400px).
     * 3. Koreksi orientasi EXIF dan BUANG seluruh metadata EXIF/GPS.
     * 4. Pemotongan presisi rasio 4:5 dengan pemusatan wajah (top-weighted).
     * 5. Buat versi utama (800x1000) dan kecil (400x500) format WebP.
     * 6. Simpan cadangan asli yang sudah bersih di disk privat.
     * 7. Hapus foto lama bila ada penggantian.
     *
     * @return array{main: string, thumb: string, original: string, version: int}
     *
     * @throws ValidationException
     */
    public function processAndStore(UploadedFile|string $file, ?string $oldPhoto = null): array
    {
        $realPath = $file instanceof UploadedFile ? $file->getRealPath() : $file;

        if (! file_exists($realPath) || ! is_readable($realPath)) {
            throw ValidationException::withMessages([
                'photo' => 'Berkas foto tidak ditemukan atau tidak dapat dibaca oleh server.',
            ]);
        }

        // 1. Validasi ukuran berkas maksimal 5 MB
        $fileSize = filesize($realPath);
        if ($fileSize > self::MAX_SIZE_BYTES) {
            throw ValidationException::withMessages([
                'photo' => 'Ukuran berkas foto terlalu besar (maksimal 5 MB).',
            ]);
        }

        // 2. Validasi gambar sungguhan via getimagesize
        $imageInfo = @getimagesize($realPath);
        if ($imageInfo === false) {
            throw ValidationException::withMessages([
                'photo' => 'Berkas yang diunggah bukan berkas gambar yang sah.',
            ]);
        }

        $mime = $imageInfo['mime'] ?? '';
        $allowedMimes = ['image/jpeg', 'image/png', 'image/webp'];
        if (! in_array($mime, $allowedMimes, true)) {
            throw ValidationException::withMessages([
                'photo' => 'Format gambar tidak didukung. Harap unggah foto dengan format JPG, PNG, atau WebP.',
            ]);
        }

        // 3. Muat gambar ke GD
        $image = match ($mime) {
            'image/jpeg' => @imagecreatefromjpeg($realPath),
            'image/png' => @imagecreatefrompng($realPath),
            'image/webp' => @imagecreatefromwebp($realPath),
            default => false,
        };

        if ($image === false) {
            throw ValidationException::withMessages([
                'photo' => 'Gagal memproses gambar. Berkas gambar mungkin rusak atau tidak kompatibel.',
            ]);
        }

        // 4. Koreksi orientasi EXIF (hanya berlaku untuk JPEG/TIFF)
        if (function_exists('exif_read_data') && ($mime === 'image/jpeg')) {
            $exif = @exif_read_data($realPath);
            if (! empty($exif['Orientation'])) {
                $image = match ((int) $exif['Orientation']) {
                    3 => imagerotate($image, 180, 0),
                    6 => imagerotate($image, -90, 0),
                    8 => imagerotate($image, 90, 0),
                    default => $image,
                };
            }
        }

        $origWidth = imagesx($image);
        $origHeight = imagesy($image);

        // 5. Validasi sisi terpendek minimal 400px
        $shortestSide = min($origWidth, $origHeight);
        if ($shortestSide < self::MIN_SIDE) {
            imagedestroy($image);
            throw ValidationException::withMessages([
                'photo' => "Ukuran gambar terlalu kecil ({$origWidth}x{$origHeight} px). Sisi terpendek gambar minimal 400 piksel agar foto pengurus tampil tajam dan proporsional.",
            ]);
        }

        // 6. Simpan cadangan asli yang sudah bersih (tanpa EXIF) di disk privat
        $hashName = Str::random(40);
        $cleanOrigCanvas = imagecreatetruecolor($origWidth, $origHeight);
        $white = imagecolorallocate($cleanOrigCanvas, 255, 255, 255);
        imagefill($cleanOrigCanvas, 0, 0, $white);
        imagecopyresampled($cleanOrigCanvas, $image, 0, 0, 0, 0, $origWidth, $origHeight, $origWidth, $origHeight);

        $tempOrig = tempnam(sys_get_temp_dir(), 'orig_');
        imagewebp($cleanOrigCanvas, $tempOrig, 90);
        imagedestroy($cleanOrigCanvas);

        $origRelativePath = "board-members-original/{$hashName}.webp";
        Storage::disk('private')->put($origRelativePath, file_get_contents($tempOrig));
        @unlink($tempOrig);

        // 7. Hitung koordinat crop rasio 4:5
        // Rasio 4:5 = 0.8
        $currentRatio = $origWidth / $origHeight;

        if ($currentRatio > 0.8) {
            // Gambar lebih lebar dari 4:5 -> potong samping kanan-kiri (pemusatan horizontal)
            $cropHeight = $origHeight;
            $cropWidth = (int) round($origHeight * 0.8);
            $srcX = (int) max(0, round(($origWidth - $cropWidth) / 2));
            $srcY = 0;
        } elseif ($currentRatio < 0.8) {
            // Gambar lebih tinggi dari 4:5 -> potong atas-bawah dengan fokus bagian atas (wajah pengurus)
            $cropWidth = $origWidth;
            $cropHeight = (int) round($origWidth / 0.8);
            $srcX = 0;
            // 15% dari selisih tinggi untuk menjaga area kepala/wajah tetap terlihat utuh
            $srcY = (int) max(0, min($origHeight - $cropHeight, round(($origHeight - $cropHeight) * 0.15)));
        } else {
            $cropWidth = $origWidth;
            $cropHeight = $origHeight;
            $srcX = 0;
            $srcY = 0;
        }

        // 8. Buat versi utama (800x1000)
        $mainCanvas = imagecreatetruecolor(self::MAIN_WIDTH, self::MAIN_HEIGHT);
        $whiteMain = imagecolorallocate($mainCanvas, 255, 255, 255);
        imagefill($mainCanvas, 0, 0, $whiteMain);
        imagecopyresampled(
            $mainCanvas,
            $image,
            0,
            0,
            $srcX,
            $srcY,
            self::MAIN_WIDTH,
            self::MAIN_HEIGHT,
            $cropWidth,
            $cropHeight
        );

        $tempMain = tempnam(sys_get_temp_dir(), 'main_');
        imagewebp($mainCanvas, $tempMain, 85);
        imagedestroy($mainCanvas);

        $mainRelativePath = "board-members/main/{$hashName}.webp";
        Storage::disk('public')->put($mainRelativePath, file_get_contents($tempMain));
        @unlink($tempMain);

        // 9. Buat versi kecil (400x500)
        $thumbCanvas = imagecreatetruecolor(self::THUMB_WIDTH, self::THUMB_HEIGHT);
        $whiteThumb = imagecolorallocate($thumbCanvas, 255, 255, 255);
        imagefill($thumbCanvas, 0, 0, $whiteThumb);

        // Resample dari versi cropped asli untuk ketajaman terbaik
        imagecopyresampled(
            $thumbCanvas,
            $image,
            0,
            0,
            $srcX,
            $srcY,
            self::THUMB_WIDTH,
            self::THUMB_HEIGHT,
            $cropWidth,
            $cropHeight
        );

        $tempThumb = tempnam(sys_get_temp_dir(), 'thumb_');
        imagewebp($thumbCanvas, $tempThumb, 82);
        imagedestroy($thumbCanvas);

        $thumbRelativePath = "board-members/thumb/{$hashName}.webp";
        Storage::disk('public')->put($thumbRelativePath, file_get_contents($tempThumb));
        @unlink($tempThumb);

        imagedestroy($image);

        // 10. Bersihkan foto lama bila ada
        if ($oldPhoto) {
            $this->deletePhotoFiles($oldPhoto);
        }

        return [
            'main' => $mainRelativePath,
            'thumb' => $thumbRelativePath,
            'original' => $origRelativePath,
            'version' => time(),
        ];
    }

    /**
     * Hapus semua berkas turunan (main, thumb) dan cadangan privat.
     */
    public function deletePhotoFiles(?string $photoPath): void
    {
        if (empty($photoPath)) {
            return;
        }

        // Hapus berkas utama di disk public
        if (Storage::disk('public')->exists($photoPath)) {
            Storage::disk('public')->delete($photoPath);
        }

        // Dapatkan nama berkas (mis: abcdef.webp)
        $filename = basename($photoPath);

        // Hapus thumbnail di disk public
        $thumbPath = "board-members/thumb/{$filename}";
        if (Storage::disk('public')->exists($thumbPath)) {
            Storage::disk('public')->delete($thumbPath);
        }

        // Hapus cadangan asli di disk private
        $originalPath = "board-members-original/{$filename}";
        if (Storage::disk('private')->exists($originalPath)) {
            Storage::disk('private')->delete($originalPath);
        }
    }

    /**
     * Dapatkan path thumbnail dari path foto utama.
     */
    public function getThumbPath(?string $photoPath): ?string
    {
        if (empty($photoPath)) {
            return null;
        }

        $filename = basename($photoPath);

        return "board-members/thumb/{$filename}";
    }

    /**
     * Dapatkan path cadangan asli privat dari path foto utama.
     */
    public function getOriginalPath(?string $photoPath): ?string
    {
        if (empty($photoPath)) {
            return null;
        }

        $filename = basename($photoPath);

        return "board-members-original/{$filename}";
    }

    /**
     * Ekstrak File ID Google Drive dari berbagai format tautan.
     */
    public function extractGoogleDriveFileId(string $url): ?string
    {
        $url = trim($url);

        // Format 1: https://drive.google.com/file/d/{id}/view...
        if (preg_match('#/file/d/([a-zA-Z0-9_-]+)#', $url, $matches)) {
            return $matches[1];
        }

        // Format 2: https://drive.google.com/open?id={id} atau ?id={id}
        if (preg_match('#[?&]id=([a-zA-Z0-9_-]+)#', $url, $matches)) {
            return $matches[1];
        }

        // Format 3: https://lh3.googleusercontent.com/d/{id}
        if (preg_match('#(?:googleusercontent\.com|drive\.google\.com)/d/([a-zA-Z0-9_-]+)#', $url, $matches)) {
            return $matches[1];
        }

        // Format 4: ID langsung (panjang minimal 25 karakter)
        if (preg_match('/^[a-zA-Z0-9_-]{25,}$/', $url)) {
            return $url;
        }

        return null;
    }

    /**
     * Unduh berkas foto dari Google Drive ke berkas sementara.
     *
     * @throws ValidationException
     */
    public function downloadFromGoogleDrive(string $urlOrId): string
    {
        $fileId = $this->extractGoogleDriveFileId($urlOrId);

        if (! $fileId) {
            throw ValidationException::withMessages([
                'google_drive_url' => 'Format tautan Google Drive tidak valid. Gunakan tautan berbagi file (contoh: https://drive.google.com/file/d/.../view).',
            ]);
        }

        if (str_contains($urlOrId, '/folders/')) {
            throw ValidationException::withMessages([
                'google_drive_url' => 'Tautan yang Anda masukkan adalah tautan Folder Google Drive, bukan tautan file foto. Buka foto yang diinginkan di Google Drive, klik Bagikan (Share) -> Salin Link file tersebut.',
            ]);
        }

        // Coba unduh dari beberapa endpoint Google Drive publik
        $endpoints = [
            "https://lh3.googleusercontent.com/d/{$fileId}",
            "https://drive.google.com/thumbnail?id={$fileId}&sz=w2500",
            "https://drive.google.com/uc?export=download&id={$fileId}",
        ];

        $body = null;

        foreach ($endpoints as $endpoint) {
            try {
                $response = \Illuminate\Support\Facades\Http::timeout(30)
                    ->withOptions([
                        'allow_redirects' => true,
                        'verify' => false,
                    ])
                    ->withHeaders([
                        'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/120.0.0.0 Safari/537.36',
                    ])
                    ->get($endpoint);

                if ($response->successful() && strlen($response->body()) > 200) {
                    $contentType = $response->header('Content-Type') ?? '';
                    $content = $response->body();

                    if (str_starts_with($contentType, 'image/') || $this->isImageBytes($content)) {
                        $body = $content;
                        break;
                    }
                }
            } catch (\Throwable) {
                // Lanjut coba endpoint berikutnya
            }
        }

        if (! $body) {
            throw ValidationException::withMessages([
                'google_drive_url' => 'Gagal mengunduh foto dari Google Drive. Pastikan: 1) File dibagikan dengan akses "Siapa saja yang memiliki link" (Anyone with the link), 2) File adalah gambar sungguhan (JPG/PNG/WebP), bukan dokumen/PDF.',
            ]);
        }

        $tempPath = tempnam(sys_get_temp_dir(), 'gdrive_photo_');
        file_put_contents($tempPath, $body);

        return $tempPath;
    }

    /**
     * Unduh dan langsung proses foto dari tautan Google Drive.
     *
     * @return array{main: string, thumb: string, original: string, version: int}
     *
     * @throws ValidationException
     */
    public function processGoogleDriveUrl(string $urlOrId, ?string $oldPhoto = null): array
    {
        $tempPath = $this->downloadFromGoogleDrive($urlOrId);

        try {
            return $this->processAndStore($tempPath, $oldPhoto);
        } finally {
            if (file_exists($tempPath)) {
                @unlink($tempPath);
            }
        }
    }

    /**
     * Periksa apakah kumpulan byte adalah berkas gambar sah (JPEG, PNG, WebP).
     */
    public function isImageBytes(string $bytes): bool
    {
        if (str_starts_with($bytes, "\xFF\xD8\xFF")) {
            return true;
        }

        if (str_starts_with($bytes, "\x89PNG\r\n\x1a\n")) {
            return true;
        }

        if (str_starts_with($bytes, 'RIFF') && str_contains(substr($bytes, 0, 16), 'WEBP')) {
            return true;
        }

        return false;
    }

    /**
     * Normalisasi nama untuk pencocokan berkas foto massal.
     * Menghapus gelar umum (H., Hj., Drs., S.Pd, dll), angka urut, simbol dan spasi berlebih.
     */
    public static function normalizeMemberName(string $name): string
    {
        $name = strtolower($name);

        // Hapus ekstensi jika ada
        $name = preg_replace('/\.(jpe?g|png|webp)$/i', '', $name);

        // Ganti underscore/dash dengan spasi
        $name = str_replace(['_', '-'], ' ', $name);

        // Hapus gelar dan sapaan umum
        $titles = [
            'h.', 'haji', 'hj.', 'hajjah', 'kh.', 'kyai', 'ustadz', 'ust.',
            'drs.', 'dr.', 'dra.', 'prof.', 's.pd.i', 's.pd', 's.ag', 's.e',
            's.sos', 's.kom', 's.h', 'm.pd', 'm.ag', 'm.e', 'm.si',
        ];

        foreach ($titles as $title) {
            $name = preg_replace('/\b' . preg_quote($title, '/') . '\b/i', '', $name);
        }

        // Hapus karakter non-alfanumerik
        $name = preg_replace('/[^a-z0-9\s]/', '', $name);

        // Hapus angka di awal nama (mis. "01 ", "1 ")
        $name = preg_replace('/^\d+\s*/', '', $name);

    /**
     * Impor banyak foto dari berkas ZIP (misal hasil download folder Google Drive).
     *
     * @return array{matched: array<string>, unmatched: array<string>, errors: array<string>}
     */
    public function importPhotosFromZip(string $zipFilePath): array
    {
        if (! class_exists('ZipArchive')) {
            throw new \RuntimeException('Ekstensi PHP ZipArchive tidak aktif.');
        }

        $zip = new \ZipArchive();
        if ($zip->open($zipFilePath) !== true) {
            throw new \RuntimeException('Gagal membuka berkas ZIP. Pastikan berkas ZIP tidak rusak.');
        }

        $tempExtractDir = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'juleha_zip_' . Str::random(10);
        @mkdir($tempExtractDir, 0755, true);
        $zip->extractTo($tempExtractDir);
        $zip->close();

        $matched = [];
        $unmatched = [];
        $errors = [];

        $boardMembers = BoardMember::where('kind', BoardMember::KIND_PERSON)->get();

        $iterator = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($tempExtractDir));

        foreach ($iterator as $file) {
            if ($file->isDir()) {
                continue;
            }

            $filename = $file->getFilename();
            $ext = strtolower($file->getExtension());
            if (! in_array($ext, ['jpg', 'jpeg', 'png', 'webp'], true)) {
                continue;
            }

            $normalizedFile = self::normalizeMemberName($filename);
            if (empty($normalizedFile)) {
                continue;
            }

            $candidate = null;
            foreach ($boardMembers as $member) {
                $normalizedMember = self::normalizeMemberName($member->name);
                if (empty($normalizedMember)) {
                    continue;
                }

                if ($normalizedFile === $normalizedMember || str_contains($normalizedFile, $normalizedMember) || str_contains($normalizedMember, $normalizedFile)) {
                    $candidate = $member;
                    break;
                }
            }

            if ($candidate) {
                try {
                    $result = $this->processAndStore($file->getPathname(), $candidate->photo);
                    $candidate->update([
                        'photo' => $result['main'],
                        'photo_version' => $result['version'],
                        'photo_consent_at' => $candidate->photo_consent_at ?? now()->toDateString(),
                        'photo_consent_note' => $candidate->photo_consent_note ?? 'Diimpor dari berkas massal ' . now()->format('d M Y'),
                    ]);
                    $matched[] = "{$candidate->name} ({$filename})";
                } catch (\Throwable $e) {
                    $errors[] = "{$candidate->name}: " . $e->getMessage();
                }
            } else {
                $unmatched[] = $filename;
            }
        }

        self::deleteDirectory($tempExtractDir);

        return [
            'matched' => $matched,
            'unmatched' => $unmatched,
            'errors' => $errors,
        ];
    }

    /**
     * Impor banyak foto dari daftar teks tautan Google Drive.
     * Format per baris: "Nama Pengurus = URL" atau "Nama: URL" atau "URL"
     *
     * @return array{matched: array<string>, unmatched: array<string>, errors: array<string>}
     */
    public function importPhotosFromBatchDriveLinks(string $linksText): array
    {
        $lines = preg_split('/\r\n|\r|\n/', trim($linksText));
        $matched = [];
        $unmatched = [];
        $errors = [];

        $boardMembers = BoardMember::where('kind', BoardMember::KIND_PERSON)->get();

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            $memberCandidate = null;
            $url = null;

            if (preg_match('/^(.*?)\s*[:=|]\s*(https?:\/\/[^\s]+)$/i', $line, $matches)) {
                $namePart = trim($matches[1]);
                $url = trim($matches[2]);
                $normalizedName = self::normalizeMemberName($namePart);

                foreach ($boardMembers as $member) {
                    $norm = self::normalizeMemberName($member->name);
                    if ($norm === $normalizedName || str_contains($norm, $normalizedName) || str_contains($normalizedName, $norm)) {
                        $memberCandidate = $member;
                        break;
                    }
                }
            }

            if ($memberCandidate && $url) {
                try {
                    $result = $this->processGoogleDriveUrl($url, $memberCandidate->photo);
                    $memberCandidate->update([
                        'photo' => $result['main'],
                        'photo_version' => $result['version'],
                        'photo_consent_at' => $memberCandidate->photo_consent_at ?? now()->toDateString(),
                        'photo_consent_note' => $memberCandidate->photo_consent_note ?? 'Diimpor dari tautan Google Drive ' . now()->format('d M Y'),
                    ]);
                    $matched[] = $memberCandidate->name;
                } catch (\Throwable $e) {
                    $errors[] = "{$memberCandidate->name}: " . $e->getMessage();
                }
            } else {
                $unmatched[] = $line;
            }
        }

        return [
            'matched' => $matched,
            'unmatched' => $unmatched,
            'errors' => $errors,
        ];
    }

    /**
     * Hapus direktori sementara secara rekursif.
     */
    public static function deleteDirectory(string $dir): void
    {
        if (! is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir) ?: [], ['.', '..']);
        foreach ($files as $file) {
            $path = $dir . DIRECTORY_SEPARATOR . $file;
            is_dir($path) ? self::deleteDirectory($path) : @unlink($path);
        }

        @rmdir($dir);
    }
}

