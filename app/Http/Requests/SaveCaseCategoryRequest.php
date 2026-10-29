<?php

namespace App\Http\Requests;

final class SaveCaseCategoryRequest extends SaveContentRequest
{
    public function contentType(): string
    {
        return 'caseCategories';
    }
}
