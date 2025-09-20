<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Mail\Mailables\Address;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class POSUEmail extends Mailable implements ShouldQueue
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
        
        // Set queue configuration
        $this->onQueue('emails');
        $this->delay(now()->addSeconds(5));

        Log::info("Email job queued: {$this->emailType} to " . ($data['email'] ?? 'unknown'));
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
            'payment_reminder' => 'POSU Citation Payment Reminder - Action Required',
            'payment_confirmation' => 'POSU Citation Payment Confirmed - Thank You'
        ];

        return new Envelope(
            from: new Address(env('MAIL_FROM_ADDRESS', 'noreply@echague.gov.ph'), env('MAIL_FROM_NAME', 'POSU Traffic Enforcement')),
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
            'welcome' => 'emails.welcome',
            'citation' => 'emails.citation',
            'password_reset' => 'emails.password-reset',
            'account_verification' => 'emails.account-verification',
            'payment_reminder' => 'emails.payment-reminder',
            'payment_confirmation' => 'emails.payment-confirmation'
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
     */
    public function attachments(): array
    {
        return [];
    }

    /**
     * Handle failures in queued jobs.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error("Failed sending email: {$this->emailType} to " . ($this->data['email'] ?? 'unknown'));
        Log::error("Exception: " . $exception->getMessage());
    }
}
