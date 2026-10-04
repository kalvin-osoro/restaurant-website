<?php
namespace App\Http\Requests;
use App\Domain\Consultations\Enums\InquiryNature;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
class StoreConsultationRequest extends FormRequest {
    public function authorize(): bool { return true; }
    public function rules(): array {
        return [
            'fullName' => ['required', 'string', 'min:2', 'max:200'],
            'organization' => ['nullable', 'string', 'max:200'],
            'email' => ['required', 'email:rfc', 'max:254'],
            'phone' => ['required', 'string', 'regex:/^\+[1-9][0-9]{6,14}$/'],
            'natureOfInquiry' => ['required', Rule::enum(InquiryNature::class)],
            'details' => ['required', 'string', 'min:10', 'max:10000'],
            'preferredOffice' => ['nullable', 'string', 'max:100'],
        ];
    }
    public function messages(): array { return ['phone.regex' => 'Use international format, for example +254712345678.']; }
}
