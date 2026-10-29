<?php

namespace App\Http\Controllers;

use App\Application\WebsiteContent\Actions\SaveContent;
use App\Http\Requests\SaveTeamRequest;
use Illuminate\Http\JsonResponse;

final class TeamWriteController extends ContentWriteController
{
    public function store(SaveTeamRequest $request, SaveContent $save): JsonResponse
    {
        return $this->persist($request, $save);
    }
}
