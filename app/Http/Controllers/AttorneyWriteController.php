<?php

namespace App\Http\Controllers;

use App\Application\WebsiteContent\Actions\SaveContent;
use App\Http\Requests\SaveAttorneyRequest;
use Illuminate\Http\JsonResponse;

final class AttorneyWriteController extends ContentWriteController
{
    public function store(SaveAttorneyRequest $request, SaveContent $save): JsonResponse
    {
        return $this->persist($request, $save);
    }
}
