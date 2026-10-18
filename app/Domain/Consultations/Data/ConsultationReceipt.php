<?php

namespace App\Domain\Consultations\Data;

final readonly class ConsultationReceipt
{
    public function __construct(public string $reference) {}
}
