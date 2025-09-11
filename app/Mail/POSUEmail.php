<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;

class POSUEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $emailType;
    public $data;

    /**
     * Create a new message instance.
     */
    public function __construct($emailType, $data = [])
    {
        $this->emailType = $emailType;
        $this->data = $data;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        $subjects = [
            'welcome' => 'Welcome to POSU Traffic Violation System',
            'citation' => 'POSU Traffic Citation Ticket Issued - Immediate Action Required',
            'password_reset' => 'Reset Your POSU Account Password',
            'account_verification' => 'Verify Your POSU Account',
            'payment_reminder' => 'POSU Citation Payment Reminder - Action Required'
        ];

        return new Envelope(
            from: new Address('noreply@echague.gov.ph', 'POSU Traffic Enforcement'),
            subject: $subjects[$this->emailType] ?? 'POSU System Notification',
            replyTo: [
                new Address('support@echague.gov.ph', 'POSU Support Team'),
            ],
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        $templates = [
            // Views exist under resources/views/emails/*.blade.php
            'welcome' => 'emails.welcome',
            'citation' => 'emails.citation',
            'password_reset' => 'emails.password-reset',
            'account_verification' => 'emails.account-verification',
            'payment_reminder' => 'emails.citation'
        ];

        return new Content(
            view: $templates[$this->emailType] ?? 'emails.welcome',
            with: [
                'data' => $this->data,
                'emailType' => $this->emailType,
            ],
        );
    }

    /**
     * Get the attachments for the message.
     *
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}