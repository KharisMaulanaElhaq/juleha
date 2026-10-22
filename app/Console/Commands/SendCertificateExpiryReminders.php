<?php

namespace App\Console\Commands;

use App\Mail\CertificateExpiryReminderMail;
use App\Models\Certificate;
use App\Models\Setting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;

class SendCertificateExpiryReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'certificates:send-expiry-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim email pengingat masa berlaku sertifikat kompetensi (180, 90, 30 hari dan hari-H) kepada peserta';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $enabled = (Setting::get('cert_reminder_enabled', '1') === '1');

        if (! $enabled) {
            $this->info('Pengingat otomatis sertifikat dinonaktifkan di pengaturan situs.');

            return self::SUCCESS;
        }

        $certificates = Certificate::where('result', Certificate::RESULT_KOMPETEN)
            ->whereNull('revoked_at')
            ->whereNotNull('valid_until')
            ->with('user')
            ->get();

        $today = now()->startOfDay();
        $sentCount = 0;

        foreach ($certificates as $certificate) {
            $user = $certificate->user;
            if (! $user || empty($user->email) || $user->isAnonymized()) {
                continue;
            }

            $validUntil = $certificate->valid_until->startOfDay();
            $daysRemaining = (int) $today->diffInDays($validUntil, false);

            $stage = null;
            if ($daysRemaining <= 0) {
                $stage = 'expired';
            } elseif ($daysRemaining <= 30) {
                $stage = '30';
            } elseif ($daysRemaining <= 90) {
                $stage = '90';
            } elseif ($daysRemaining <= 180) {
                $stage = '180';
            }

            if (! $stage) {
                continue;
            }

            $remindersSent = $certificate->reminders_sent ?? [];

            // Kirim hanya satu kali per tahap
            if (! in_array($stage, $remindersSent, true)) {
                try {
                    Mail::to($user->email)->queue(new CertificateExpiryReminderMail($certificate, $stage));
                    $remindersSent[] = $stage;
                    $certificate->reminders_sent = $remindersSent;
                    $certificate->saveQuietly();
                    $sentCount++;

                    $this->line("Pengingat tahap [{$stage}] dikirim ke: {$user->email} (Sertifikat: {$certificate->certificate_number})");
                } catch (\Throwable $e) {
                    $this->error("Gagal mengirim ke {$user->email}: ".$e->getMessage());
                }
            }
        }

        $this->info("Proses selesai. Sebanyak {$sentCount} email pengingat telah dimasukkan ke antrean.");

        return self::SUCCESS;
    }
}
