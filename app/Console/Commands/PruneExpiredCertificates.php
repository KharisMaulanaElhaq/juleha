<?php

namespace App\Console\Commands;

use App\Models\AuditLog;
use App\Models\Certificate;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class PruneExpiredCertificates extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'certificates:prune-expired-records';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Hapus catatan sertifikat yang telah melewati batas masa retensi setelah masa berlaku habis';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $rawYears = Setting::get('cert_record_retention_years');

        if ($rawYears === null || $rawYears === '' || ! is_numeric($rawYears) || (int) $rawYears <= 0) {
            $this->info('Pengaturan cert_record_retention_years kosong / belum ditetapkan. Sesuai kebijakan bersama LSP, sistem tidak menghapus catatan sertifikat apa pun.');

            return self::SUCCESS;
        }

        $years = (int) $rawYears;
        $cutoffDate = now()->subYears($years)->toDateString();

        $this->info("Menjalankan pembersihan retensi catatan sertifikat (Masa retensi: {$years} tahun, Batas tanggal: {$cutoffDate})...");

        $query = Certificate::withTrashed()
            ->whereNotNull('valid_until')
            ->where('valid_until', '<=', $cutoffDate);

        $count = $query->count();

        if ($count === 0) {
            $this->info('Tidak ada catatan sertifikat yang melewati batas masa retensi.');

            return self::SUCCESS;
        }

        $disk = Storage::disk('private');
        $deletedCount = 0;

        $query->chunkById(100, function ($certificates) use ($disk, &$deletedCount) {
            foreach ($certificates as $cert) {
                // Hapus berkas fisik jika masih ada di storage privat
                if ($cert->file_path && $disk->exists($cert->file_path)) {
                    $disk->delete($cert->file_path);
                }

                AuditLog::logCertificateActivity('retention_pruned', $cert->id, [
                    'certificate_number' => $cert->certificate_number,
                    'public_code' => $cert->public_code,
                    'valid_until' => $cert->valid_until?->toDateString(),
                ]);

                // Hapus catatan sertifikat permanen
                $cert->forceDelete();
                $deletedCount++;
            }
        });

        $this->info("Berhasil membersihkan {$deletedCount} catatan sertifikat yang melewati batas retensi.");

        return self::SUCCESS;
    }
}
