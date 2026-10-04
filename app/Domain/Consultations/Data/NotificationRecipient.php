<?php

namespace App\Domain\Consultations\Data;

// Only the information a notification provider needs; excludes confidential matter details.
final readonly class NotificationRecipient
{
    public function __construct(
        public string $reference,
        public string $email,
        public string $phone,
    ) {}
}
