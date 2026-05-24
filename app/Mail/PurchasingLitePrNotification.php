<?php

namespace App\Mail;

use App\Models\PurchaseRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PurchasingLitePrNotification extends Mailable
{
    use Queueable, SerializesModels;

    public PurchaseRequest $purchaseRequest;

    public string $mailSubject;

    public string $title;

    public string $messageText;

    public string $buttonLabel;

    public string $buttonUrl;

    public ?string $remarks;

    /**
     * Create a new message instance.
     */
    public function __construct(
        PurchaseRequest $purchaseRequest,
        string $mailSubject,
        string $title,
        string $messageText,
        string $buttonLabel,
        string $buttonUrl,
        ?string $remarks = null
    ) {
        $this->purchaseRequest = $purchaseRequest;
        $this->mailSubject = $mailSubject;
        $this->title = $title;
        $this->messageText = $messageText;
        $this->buttonLabel = $buttonLabel;
        $this->buttonUrl = $buttonUrl;
        $this->remarks = $remarks;
    }

    /**
     * Get the message envelope.
     */
    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->mailSubject,
        );
    }

    /**
     * Get the message content definition.
     */
    public function content(): Content
    {
        return new Content(
            view: 'emails.purchasing-lite.pr-notification',
            with: [
                'purchaseRequest' => $this->purchaseRequest,
                'title' => $this->title,
                'messageText' => $this->messageText,
                'buttonLabel' => $this->buttonLabel,
                'buttonUrl' => $this->buttonUrl,
                'remarks' => $this->remarks,
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
}
