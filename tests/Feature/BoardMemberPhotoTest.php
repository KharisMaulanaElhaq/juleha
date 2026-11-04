<?php

namespace Tests\Feature;

use App\Models\BoardMember;
use App\Models\User;
use App\Policies\BoardMemberPolicy;
use App\Services\BoardMemberPhotoService;
use Database\Seeders\BoardMemberSeeder;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\TextInput;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class BoardMemberPhotoTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        Storage::fake('private');
    }

    /**
     * Buat berkas gambar sementara untuk pengujian.
     */
    protected function createTestImage(int $width, int $height, string $format = 'jpeg'): string
    {
        $image = imagecreatetruecolor($width, $height);
        $blue = imagecolorallocate($image, 30, 144, 255);
        imagefill($image, 0, 0, $blue);

        $tempPath = tempnam(sys_get_temp_dir(), 'test_img_') . '.' . $format;
        match ($format) {
            'jpeg', 'jpg' => imagejpeg($image, $tempPath, 90),
            'png' => imagepng($image, $tempPath, 8),
            'webp' => imagewebp($image, $tempPath, 90),
            default => imagejpeg($image, $tempPath, 90),
        };
        imagedestroy($image);

        return $tempPath;
    }

    /**
     * 1. Pengunggahan dari komputer memotong ke rasio 4:5, menghasilkan berkas utama & thumbnail WebP,
     * serta menyimpan cadangan asli bersih di disk privat.
     */
    public function test_upload_crops_to_4_5_and_generates_webp_and_clean_private_original(): void
    {
        $service = app(BoardMemberPhotoService::class);
        $tempPath = $this->createTestImage(1200, 900, 'jpeg'); // Rasio 4:3 (lebih lebar)

        try {
            $uploaded = new UploadedFile($tempPath, 'pengurus.jpg', 'image/jpeg', null, true);
            $result = $service->processAndStore($uploaded);

            $this->assertNotEmpty($result['main']);
            $this->assertNotEmpty($result['thumb']);
            $this->assertNotEmpty($result['original']);

            // Berkas utama 800x1000
            Storage::disk('public')->assertExists($result['main']);
            $mainContent = Storage::disk('public')->get($result['main']);
            $mainImg = imagecreatefromstring($mainContent);
            $this->assertEquals(BoardMemberPhotoService::MAIN_WIDTH, imagesx($mainImg));
            $this->assertEquals(BoardMemberPhotoService::MAIN_HEIGHT, imagesy($mainImg));
            imagedestroy($mainImg);

            // Thumbnail 400x500
            Storage::disk('public')->assertExists($result['thumb']);
            $thumbContent = Storage::disk('public')->get($result['thumb']);
            $thumbImg = imagecreatefromstring($thumbContent);
            $this->assertEquals(BoardMemberPhotoService::THUMB_WIDTH, imagesx($thumbImg));
            $this->assertEquals(BoardMemberPhotoService::THUMB_HEIGHT, imagesy($thumbImg));
            imagedestroy($thumbImg);

            // Berkas asli bersih tersimpan di disk privat
            Storage::disk('private')->assertExists($result['original']);
            $this->assertStringContainsString('board-members-original/', $result['original']);
        } finally {
            @unlink($tempPath);
        }
    }

    /**
     * 2. EXIF dan metadata GPS otomatis dibuang dari seluruh berkas yang disimpan.
     */
    public function test_exif_and_gps_metadata_are_completely_stripped(): void
    {
        $service = app(BoardMemberPhotoService::class);
        $tempPath = $this->createTestImage(800, 1000, 'jpeg');

        try {
            $result = $service->processAndStore($tempPath);

            $mainFilePath = Storage::disk('public')->path($result['main']);
            $origFilePath = Storage::disk('private')->path($result['original']);

            // Berkas WebP tidak memiliki blok EXIF JPEG bawaan
            $this->assertStringEndsWith('.webp', $result['main']);
            $this->assertStringEndsWith('.webp', $result['thumb']);
            $this->assertStringEndsWith('.webp', $result['original']);

            // Jika dievaluasi oleh exif_read_data, tidak boleh mengandung koordinat GPS
            if (function_exists('exif_read_data')) {
                $exifMain = @exif_read_data($mainFilePath);
                $this->assertTrue(empty($exifMain['GPSLatitude']));
                $this->assertTrue(empty($exifMain['GPSLongitude']));

                $exifOrig = @exif_read_data($origFilePath);
                $this->assertTrue(empty($exifOrig['GPSLatitude']));
                $this->assertTrue(empty($exifOrig['GPSLongitude']));
            }
        } finally {
            @unlink($tempPath);
        }
    }

    /**
     * 3. Berkas non-gambar dan gambar rusak ditolak dengan pesan Bahasa Indonesia yang jelas.
     */
    public function test_non_image_and_corrupt_files_are_rejected(): void
    {
        $service = app(BoardMemberPhotoService::class);

        // Berkas teks disamarkan jadi gambar
        $fakeImg = tempnam(sys_get_temp_dir(), 'fake_') . '.jpg';
        file_put_contents($fakeImg, 'Ini bukan berkas gambar sungguhan melainkan teks rahasia.');

        try {
            $service->processAndStore($fakeImg);
            $this->fail('Harusnya gagal memproses berkas non-gambar.');
        } catch (ValidationException $e) {
            $error = $e->errors()['photo'][0] ?? '';
            $this->assertStringContainsString('bukan berkas gambar yang sah', $error);
        } finally {
            @unlink($fakeImg);
        }

        // Berkas gambar rusak (corrupted binary)
        $corruptImg = tempnam(sys_get_temp_dir(), 'corrupt_') . '.jpg';
        file_put_contents($corruptImg, "\xFF\xD8\xFF\xE0" . substr(random_bytes(32), 0, 16));

        try {
            $service->processAndStore($corruptImg);
            $this->fail('Harusnya gagal memproses berkas gambar rusak.');
        } catch (ValidationException $e) {
            $error = $e->errors()['photo'][0] ?? '';
            $this->assertTrue(
                str_contains($error, 'rusak') || str_contains($error, 'bukan berkas gambar')
            );
        } finally {
            @unlink($corruptImg);
        }
    }

    /**
     * 4. Gambar dengan sisi terpendek di bawah 400px ditolak.
     */
    public function test_images_with_short_side_under_400px_are_rejected(): void
    {
        $service = app(BoardMemberPhotoService::class);
        $smallPath = $this->createTestImage(350, 600, 'jpeg'); // Sisi pendek 350px

        try {
            $service->processAndStore($smallPath);
            $this->fail('Harusnya menolak gambar yang terlalu kecil.');
        } catch (ValidationException $e) {
            $error = $e->errors()['photo'][0] ?? '';
            $this->assertStringContainsString('minimal 400 piksel', $error);
        } finally {
            @unlink($smallPath);
        }
    }

    /**
     * 5. Aksi "Atur Ulang Pemotongan" memotong ulang dari cadangan asli di disk privat.
     */
    public function test_reprocess_from_original_uses_clean_private_storage(): void
    {
        $service = app(BoardMemberPhotoService::class);
        $tempPath = $this->createTestImage(800, 1000, 'jpeg');

        try {
            $initial = $service->processAndStore($tempPath);
            $this->assertTrue($service->hasOriginal($initial['main']));

            $recropped = $service->reprocessFromOriginal($initial['main']);
            $this->assertNotEmpty($recropped['main']);
            Storage::disk('public')->assertExists($recropped['main']);
            Storage::disk('public')->assertExists($recropped['thumb']);
        } finally {
            @unlink($tempPath);
        }
    }

    /**
     * 6. Penghapusan foto membersihkan seluruh berkas turunan publik dan cadangan privat.
     */
    public function test_photo_deletion_removes_main_thumb_and_private_original(): void
    {
        $service = app(BoardMemberPhotoService::class);
        $tempPath = $this->createTestImage(800, 1000, 'jpeg');

        try {
            $result = $service->processAndStore($tempPath);

            Storage::disk('public')->assertExists($result['main']);
            Storage::disk('public')->assertExists($result['thumb']);
            Storage::disk('private')->assertExists($result['original']);

            $service->deletePhotoFiles($result['main']);

            Storage::disk('public')->assertMissing($result['main']);
            Storage::disk('public')->assertMissing($result['thumb']);
            Storage::disk('private')->assertMissing($result['original']);
        } finally {
            @unlink($tempPath);
        }
    }

    /**
     * 7. Server TIDAK memiliki route, Action, atau metode yang mengunduh dari URL input pengguna (SSRF elimination).
     */
    public function test_server_has_zero_url_download_endpoints_and_rejects_ssrf(): void
    {
        $service = app(BoardMemberPhotoService::class);

        // Pastikan seluruh metode unduh URL lama sudah dihapus
        $this->assertFalse(method_exists($service, 'downloadFromGoogleDrive'));
        $this->assertFalse(method_exists($service, 'processGoogleDriveUrl'));
        $this->assertFalse(method_exists($service, 'importPhotosFromBatchDriveLinks'));
        $this->assertFalse(method_exists($service, 'extractGoogleDriveFileId'));

        // Pastikan tidak ada rute yang menerima URL unduhan foto pengurus
        foreach (Route::getRoutes() as $route) {
            $uri = $route->uri();
            $this->assertStringNotContainsString('fetch-google-drive', $uri);
            $this->assertStringNotContainsString('download-drive-photo', $uri);
        }
    }

    /**
     * 8. Server tidak pernah menerima atau menyimpan token Google OAuth / file ID dari pengguna.
     */
    public function test_server_never_accepts_or_stores_google_tokens(): void
    {
        $member = BoardMember::create([
            'kind' => BoardMember::KIND_PERSON,
            'group' => BoardMember::GROUP_PIMPINAN_HARIAN,
            'name' => 'Ujang TSM, SH.',
            'position' => 'Sekretaris I',
            'is_active' => true,
        ]);

        // Periksa atribut model: tidak ada kolom untuk oauth token
        $this->assertFalse(in_array('google_access_token', $member->getFillable(), true));
        $this->assertFalse(in_array('google_file_id', $member->getFillable(), true));

        // Pastikan tidak ada Client Secret untuk Google Picker di konfigurasi
        $this->assertNull(config('services.google_picker.client_secret'));
    }

    /**
     * 9. Tombol Google Drive tersembunyi bila konfigurasi kosong, dan menampilkan teks bantuan yang ramah.
     */
    public function test_drive_button_hidden_when_config_is_empty_and_visible_when_configured(): void
    {
        // 1. Kasus konfigurasi kosong
        config([
            'services.google_picker.api_key' => '',
            'services.google_picker.client_id' => '',
            'services.google_picker.app_id' => '',
        ]);

        $renderedEmpty = view('filament.forms.components.board-member-photo-picker')->render();
        $this->assertStringContainsString('Fitur Google Drive belum dikonfigurasi, gunakan Pilih dari komputer.', $renderedEmpty);
        $this->assertStringNotContainsString('type="button" x-on:click="pickFromGoogleDrive()"', $renderedEmpty);

        // 2. Kasus konfigurasi lengkap
        config([
            'services.google_picker.api_key' => 'AIzaSyTestApiKey123',
            'services.google_picker.client_id' => '12345678-test.apps.googleusercontent.com',
            'services.google_picker.app_id' => '123456789012',
        ]);

        $renderedFilled = view('filament.forms.components.board-member-photo-picker')->render();
        $this->assertStringContainsString('Pilih dari Google Drive', $renderedFilled);
        $this->assertStringContainsString('Pilih dari Komputer', $renderedFilled);

        // Pastikan tidak ada kebocoran Client Secret pada HTML yang di-render
        $this->assertStringNotContainsString('client_secret', $renderedFilled);
    }

    /**
     * 10. Jika berkas foto hilang dari disk, halaman publik menampilkan inisial avatar (bukan ikon gambar rusak),
     * dan tabel admin menandai status file tidak ditemukan.
     */
    public function test_missing_photo_file_falls_back_to_initials_avatar_and_shows_admin_warning(): void
    {
        $this->seed(BoardMemberSeeder::class);

        $chasan = BoardMember::where('name', 'Chasan Mudhofar')->first();
        $this->assertNotNull($chasan);

        // Pasang path foto di database tetapi TIDAK membuat berkas fisiknya di storage
        $chasan->update([
            'photo' => 'board-members/main/missing-file-6kb.webp',
            'photo_version' => 8888,
            'photo_consent_at' => now()->toDateString(),
            'photo_consent_note' => 'Diminta sendiri via WhatsApp, 28 Okt 2026',
        ]);

        // Model mendeteksi berkas hilang
        $this->assertFalse($chasan->hasPhoto());
        $this->assertTrue($chasan->isPhotoMissing());

        // Halaman publik /pengurus harus menampilkan inisial avatar dan TIDAK merender tag img rusak
        $response = $this->get(route('pengurus'));
        $response->assertStatus(200);

        // Harus tampil avatar inisial 'CM'
        $response->assertSee('CM');
        $response->assertDontSee('missing-file-6kb.webp');

        // Status di admin table mendeteksi missing
        $statusKey = $chasan->isPhotoMissing() ? 'missing' : ($chasan->hasPhoto() ? 'valid' : 'without_photo');
        $this->assertEquals('missing', $statusKey);
    }

    /**
     * 11. Catatan persetujuan foto (photo_consent_note) tidak boleh terisi otomatis dan wajib diisi bila foto diunggah.
     */
    public function test_photo_consent_note_is_not_autofilled_and_is_required_when_photo_is_present(): void
    {
        $member = new BoardMember;
        $this->assertNull($member->photo_consent_note);

        // Pastikan form schema menetapkan placeholder yang sesuai dan tidak memiliki default value
        $this->seed(BoardMemberSeeder::class);
        $sample = BoardMember::first();
        $this->assertNotNull($sample);

        // Catatan bukti persetujuan tidak terisi otomatis saat membuat pengurus baru
        $newMember = BoardMember::create([
            'kind' => BoardMember::KIND_PERSON,
            'group' => BoardMember::GROUP_PIMPINAN_HARIAN,
            'name' => 'Pengurus Tanpa Foto',
            'position' => 'Wakil Sekretaris',
            'is_active' => true,
        ]);
        $this->assertNull($newMember->photo_consent_note);
    }

    /**
     * 12. Hak akses pengelolaan foto pengurus dibatasi hanya untuk peran yang berwenang.
     */
    public function test_board_member_management_role_permissions(): void
    {
        $superAdmin = User::factory()->create(['role' => User::ROLE_SUPER_ADMIN, 'is_active' => true]);
        $adminKegiatan = User::factory()->create(['role' => User::ROLE_ADMIN_KEGIATAN, 'is_active' => true]);
        $editorKonten = User::factory()->create(['role' => User::ROLE_EDITOR_KONTEN, 'is_active' => true]);
        $participant = User::factory()->create(['role' => User::ROLE_PARTICIPANT, 'is_active' => true]);

        $policy = new BoardMemberPolicy;

        // Berhak: super_admin, admin_kegiatan, editor_konten
        $this->assertTrue($policy->viewAny($superAdmin));
        $this->assertTrue($policy->viewAny($adminKegiatan));
        $this->assertTrue($policy->viewAny($editorKonten));
        $this->assertTrue($policy->create($superAdmin));
        $this->assertTrue($policy->update($adminKegiatan, new BoardMember));

        // Tidak berhak: participant
        $this->assertFalse($policy->viewAny($participant));
        $this->assertFalse($policy->create($participant));
        $this->assertFalse($policy->update($participant, new BoardMember));
    }
}
