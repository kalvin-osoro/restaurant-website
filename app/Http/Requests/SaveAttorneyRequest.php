<?php

namespace App\Http\Requests;

final class SaveAttorneyRequest extends SaveContentRequest
{
    public function contentType(): string
    {
        return 'attorneys';
    }
}
