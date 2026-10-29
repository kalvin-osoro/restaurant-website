<?php

namespace App\Http\Requests;

final class SavePracticeAreaRequest extends SaveContentRequest
{
    public function contentType(): string
    {
        return 'practiceAreas';
    }
}
