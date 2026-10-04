<?php

namespace App\Domain\Consultations\Contracts;

use App\Domain\Consultations\Data\ConsultationData;
use App\Domain\Consultations\Data\ConsultationReceipt;

interface ConsultationRepository
{
    /** Persist the inquiry and enabled notification outbox records atomically. */
    public function book(ConsultationData $data): ConsultationReceipt;
}
