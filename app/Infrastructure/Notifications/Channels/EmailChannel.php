<?php
namespace App\Infrastructure\Notifications\Channels;
use App\Domain\Consultations\Contracts\ConsultationChannel;
use App\Domain\Consultations\Data\NotificationRecipient;
use Illuminate\Support\Facades\Mail;
class EmailChannel implements ConsultationChannel {
    public function send(NotificationRecipient $consultation, string $channel, string $idempotencyKey): ?string {
        Mail::to($consultation->email)->send(new \App\Infrastructure\Notifications\Mail\ConsultationReceived($consultation->reference));
        return null;
    }
}
