<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ContactFormSubmission extends Mailable
{
    use Queueable, SerializesModels;

    public string $email;
    public ?string $phone;
    public string $subjectLine;
    public string $bodyMessage;
    public ?string $ip;
    public ?string $userAgent;

    public function __construct(
        string $email,
        ?string $phone,
        string $subjectLine,
        string $bodyMessage,
        ?string $ip = null,
        ?string $userAgent = null
    ) {
        $this->email = $email;
        $this->phone = $phone;
        $this->subjectLine = $subjectLine;
        $this->bodyMessage = $bodyMessage;
        $this->ip = $ip;
        $this->userAgent = $userAgent;
    }

    public function envelope(): Envelope
    {
        $prefix = (string) (config('mail.contact_subject_prefix') ?? '');
        $prefix = trim($prefix);
        $finalSubject = ($prefix !== '' ? $prefix . ' ' : '') . 'New contact request — ' . config('app.name');

        return new Envelope(
            subject: $finalSubject,
            replyTo: [new Address($this->email)],
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.contact-form-submission',
            with: [
                'email' => $this->email,
                'phone' => $this->phone,
                'subjectLine' => $this->subjectLine,
                'bodyMessage' => $this->bodyMessage,
                'ip' => $this->ip,
                'userAgent' => $this->userAgent,
            ],
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
