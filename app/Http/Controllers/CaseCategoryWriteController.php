<?php

namespace App\Http\Controllers;

use App\Application\WebsiteContent\Actions\SaveContent;
use App\Http\Requests\SaveCaseCategoryRequest;
use Illuminate\Http\JsonResponse;

final class CaseCategoryWriteController extends ContentWriteController
{
    public function store(SaveCaseCategoryRequest $request, SaveContent $save): JsonResponse
    {
        return $this->persist($request, $save);
    }
}
