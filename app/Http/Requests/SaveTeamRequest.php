<?php

namespace App\Http\Requests;

final class SaveTeamRequest extends SaveContentRequest
{
    public function contentType(): string
    {
        return 'attorneys';
    }
}
