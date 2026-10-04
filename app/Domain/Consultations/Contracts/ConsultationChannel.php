<?php
namespace App\Domain\Consultations\Contracts;
use App\Domain\Consultations\Data\NotificationRecipient;
interface ConsultationChannel {
    /** Return a provider reference when available. Never include confidential matter details. */
    public function send(NotificationRecipient $consultation, string $channel, string $idempotencyKey): ?string;
}
