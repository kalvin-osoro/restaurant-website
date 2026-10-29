<?php

namespace App\Http\Requests;

final class SaveCaseStudyRequest extends SaveContentRequest
{
    public function contentType(): string
    {
        return 'cases';
    }
}
