<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ListAttorneysRequest extends FormRequest
{
    public function authorize(): bool { return true; }

    public function rules(): array
    {
        return [
            'q' => ['sometimes', 'required', 'string', 'max:100'],
            'practiceArea' => ['sometimes', 'required', 'string', 'max:100'],
            'page' => ['sometimes', 'integer', 'min:1', 'max:100000'],
            'perPage' => ['sometimes', 'integer', 'min:1', 'max:100'],
        ];
    }
}
