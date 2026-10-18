<?php

namespace App\Application\Consultations\Actions;

use App\Domain\Consultations\Contracts\ConsultationRepository;
use App\Domain\Consultations\Data\ConsultationData;
use App\Domain\Consultations\Data\ConsultationReceipt;

final readonly class BookConsultation
{
    public function __construct(private ConsultationRepository $consultations) {}

    public function execute(ConsultationData $data): ConsultationReceipt
    {
        return $this->consultations->book($data);
    }
}
