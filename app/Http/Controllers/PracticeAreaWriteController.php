<?php

namespace App\Http\Controllers;

use App\Application\WebsiteContent\Actions\SaveContent;
use App\Http\Requests\SavePracticeAreaRequest;
use Illuminate\Http\JsonResponse;

final class PracticeAreaWriteController extends ContentWriteController
{
    public function store(SavePracticeAreaRequest $request, SaveContent $save): JsonResponse
    {
        return $this->persist($request, $save);
    }
}
