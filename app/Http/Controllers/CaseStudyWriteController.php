<?php

namespace App\Http\Controllers;

use App\Application\WebsiteContent\Actions\SaveContent;
use App\Http\Requests\SaveCaseStudyRequest;
use Illuminate\Http\JsonResponse;

final class CaseStudyWriteController extends ContentWriteController
{
    public function store(SaveCaseStudyRequest $request, SaveContent $save): JsonResponse
    {
        return $this->persist($request, $save);
    }
}
