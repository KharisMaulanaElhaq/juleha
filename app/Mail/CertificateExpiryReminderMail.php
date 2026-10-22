<?php

namespace App\Mail;

use App\Models\Certificate;
use App\Models\Setting;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class CertificateExpiryReminderMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Certificate $certificate,
        public string $stage // '180', '90', '30', atau 'expired'
    ) {}

    public function envelope(): Envelope
    {
        $subject = match ($this->stage) {
            '180' => 'Pengingat: 180 Hari Menjelang Berakhirnya Sertifikat Kompetensi '.$this->certificate->scheme_name,
            '90' => 'Penting: 90 Hari Menjelang Berakhirnya Sertifikat Kompetensi '.$this->certificate->scheme_name,
            '30' => 'Peringatan: 30 Hari Menjelang Berakhirnya Sertifikat Kompetensi '.$this->certificate->scheme_name,
            'expired' => 'Pemberitahuan: Masa Berlaku Sertifikat Kompetensi Telah Berakhir',
            default => 'Pemberitahuan Masa Berlaku Sertifikat Kompetensi BNSP',
        };

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        $rawWa = Setting::get('whatsapp_number', '082324448886');
        $cleanWa = preg_replace('/\D/', '', $rawWa);
        if (str_starts_with($cleanWa, '0')) {
            $cleanWa = '62'.substr($cleanWa, 1);
        }

        return new Content(
            view: 'emails.certificate-expiry-reminder',
            with: [
                'certificate' => $this->certificate,
                'user' => $this->certificate->user,
                'stage' => $this->stage,
                'waNumber' => $cleanWa,
            ],
        );
    }
}
