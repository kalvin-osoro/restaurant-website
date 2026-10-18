<?php

namespace App\Domain\Consultations\Data;

final readonly class ConsultationData
{
    public function __construct(
        public string $fullName,
        public ?string $organization,
        public string $email,
        public string $phone,
        public string $natureOfInquiry,
        public string $details,
        public ?string $preferredOffice,
    ) {}
}
