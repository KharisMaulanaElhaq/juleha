<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AccountStatusChangedNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public User $targetUser,
        public bool $isActive,
    ) {}

    public function envelope(): Envelope
    {
        $subject = $this->isActive
            ? 'Akun Anda Telah Aktif Kembali – DPD JULEHA Brebes'
            : 'Pemberitahuan Status Akun – DPD JULEHA Brebes';

        return new Envelope(subject: $subject);
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.account-status-changed',
            with: [
                'user' => $this->targetUser,
                'isActive' => $this->isActive,
            ],
        );
    }
}
