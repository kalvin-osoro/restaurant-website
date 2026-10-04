<?php
namespace App\Infrastructure\Notifications\Mail;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
class ConsultationReceived extends Mailable {
    public function __construct(public string $reference) {}
    public function envelope(): Envelope { return new Envelope(subject: 'Consultation inquiry received'); }
    public function content(): Content { return new Content(text: 'mail.consultation-received'); }
}
