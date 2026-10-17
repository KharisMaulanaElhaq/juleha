<?php

namespace App\Notifications;

use App\Models\Registration;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewRegistrationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public Registration $registration) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $reg = $this->registration;
        $eventTitle = $reg->event?->title ?? 'Bimtek JULEHA Brebes';

        return (new MailMessage)
            ->subject("[Pendaftar Baru] {$eventTitle} - {$reg->full_name} ({$reg->code})")
            ->greeting('Halo Pengurus DPD JULEHA Brebes,')
            ->line("Ada pendaftar baru untuk kegiatan: **{$eventTitle}**")
            ->line("**Kode Registrasi:** {$reg->code}")
            ->line("**Nama Lengkap:** {$reg->full_name}")
            ->line("**Nomor WhatsApp:** {$reg->phone}")
            ->line("**Kecamatan:** {$reg->district}")
            ->line("**Kategori Peserta:** {$reg->participant_type_label}")
            ->action('Buka Panel Admin', url('/admin/registrations'))
            ->line('Silakan verifikasi pendaftar melalui panel administrasi.');
    }
}
