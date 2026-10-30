<?php

namespace App\Http\Requests;

use App\Domain\Identity\Role;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AssignRoleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->can('users.assign-roles') ?? false;
    }

    public function rules(): array
    {
        return ['role' => ['required', Rule::enum(Role::class)]];
    }
}
