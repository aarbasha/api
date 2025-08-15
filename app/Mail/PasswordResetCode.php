<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Queue\SerializesModels;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Support\Facades\Storage;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Contracts\Queue\ShouldQueue;

class PasswordResetCode extends Mailable
{
    use Queueable, SerializesModels;


    public $code;
    public $email;
    public $pathLogo;


    /**
     * Create a new message instance.
     */
    public function __construct($code, $email, $pathLogo)
    {
        $this->code = $code;
        $this->email = $email;
        $this->pathLogo  = $pathLogo;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Password Reset Code',
        );
    }


    public function attachments(): array
    {
        return [
            // Attachment::fromPath(storage_path("app/public/{$imagePath}"))
            //     ->as('logo.png')
            //     ->withMime('image/png'),
        ];
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.password-reset-code',
            with: [
                'code' => $this->code,
                'email' => $this->email,
                'logoUrl' => $this->pathLogo,
            ],
        );
    }
}
