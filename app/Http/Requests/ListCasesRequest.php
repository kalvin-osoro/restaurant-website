<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListCasesRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'category' => ['sometimes', 'required', 'string', 'max:100'],
            'featured' => ['sometimes', 'boolean'],
            'page' => ['sometimes', 'integer', 'min:1', 'max:100000'],
            'perPage' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
