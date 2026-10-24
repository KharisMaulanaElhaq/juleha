<?php

namespace App\Mail;

use App\Models\DataRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class DataRequestSubmittedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public DataRequest $dataRequest
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Tanda Terima Permohonan Hak Data Pribadi [{$this->dataRequest->request_number}] – DPD JULEHA Brebes"
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.data-request-submitted',
            with: [
                'request' => $this->dataRequest,
                'officerName' => setting('privacy_officer_name', 'Sekretariat DPD JULEHA Brebes'),
                'contactEmail' => setting('privacy_contact_email') ?: setting('admin_email', 'dpdjulehabrebesofficial@gmail.com'),
            ]
        );
    }
}
