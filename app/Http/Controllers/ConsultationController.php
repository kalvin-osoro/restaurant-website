<?php

namespace App\Http\Controllers;

use App\Application\Consultations\Actions\BookConsultation;
use App\Domain\Consultations\Data\ConsultationData;
use App\Http\Requests\StoreConsultationRequest;
use Illuminate\Http\JsonResponse;

class ConsultationController extends Controller
{
    public function store(StoreConsultationRequest $request, BookConsultation $book): JsonResponse
    {
        $input = $request->validated();
        $receipt = $book->execute(new ConsultationData(
            fullName: $input['fullName'],
            organization: $input['organization'] ?? null,
            email: $input['email'],
            phone: $input['phone'],
            natureOfInquiry: $input['natureOfInquiry'],
            details: $input['details'],
            preferredOffice: $input['preferredOffice'] ?? null,
        ));

        return response()->json([
            'success' => true,
            'id' => $receipt->reference,
            'referenceNumber' => $receipt->reference,
            'message' => 'Your consultation inquiry has been received. Our team will contact you to arrange a time.',
        ], 201);
    }
}
