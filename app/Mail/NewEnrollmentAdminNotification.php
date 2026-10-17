<?php

namespace App\Mail;

use App\Models\Enrollment;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewEnrollmentAdminNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public Enrollment $enrollment) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Pendaftar Baru Kegiatan: '.($this->enrollment->event?->title ?? 'Kegiatan JULEHA'),
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-enrollment-admin',
            with: [
                'enrollment' => $this->enrollment,
                'user' => $this->enrollment->user,
                'event' => $this->enrollment->event,
                'profile' => $this->enrollment->user?->profile,
            ],
        );
    }
}
