<?php

namespace App\Mail;

use App\Models\Material;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NewMaterialNotification extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Material $material,
        public User $participant,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Materi Baru Tersedia: '.$this->material->title.' ('.($this->material->event?->title ?? 'Kegiatan JULEHA').')',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.new-material-notification',
            with: [
                'material' => $this->material,
                'participant' => $this->participant,
                'event' => $this->material->event,
            ],
        );
    }
}
