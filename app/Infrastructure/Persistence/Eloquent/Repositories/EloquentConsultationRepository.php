<?php

namespace App\Infrastructure\Persistence\Eloquent\Repositories;

use App\Domain\Consultations\Contracts\ConsultationRepository;
use App\Domain\Consultations\Data\ConsultationData;
use App\Domain\Consultations\Data\ConsultationReceipt;
use App\Infrastructure\Persistence\Eloquent\Models\Consultation;
use App\Infrastructure\Persistence\Eloquent\Models\NotificationLog;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

final class EloquentConsultationRepository implements ConsultationRepository
{
    public function book(ConsultationData $data): ConsultationReceipt
    {
        return DB::transaction(function () use ($data): ConsultationReceipt {
            $consultation = Consultation::create([
                'reference' => (string) Str::uuid(),
                'full_name' => $data->fullName,
                'organization' => $data->organization,
                'email' => $data->email,
                'phone' => $data->phone,
                'nature_of_inquiry' => $data->natureOfInquiry,
                'details' => $data->details,
                'preferred_office' => $data->preferredOffice,
            ]);

            foreach (config('consultations.channels', []) as $channel => $settings) {
                if (!($settings['enabled'] ?? false)) {
                    continue;
                }

                NotificationLog::create([
                    'consultation_id' => $consultation->id,
                    'channel' => $channel,
                ]);
            }

            return new ConsultationReceipt($consultation->reference);
        });
    }
}
