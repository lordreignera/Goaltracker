<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isSuperAdmin();
    }

    public function rules(): array
    {
        $user = $this->route('user');

        return [
            'first_name' => ['required', 'string', 'max:100'],
            'second_name' => ['required', 'string', 'max:100'],
            'phone_number' => ['required', 'string', 'max:30', Rule::unique('users', 'phone_number')->ignore($user?->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user?->id)],
            'department_id' => ['required', 'exists:departments,id'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'requested_role' => ['required', Rule::in(['Staff', 'Supervisor', 'Manager', 'Admin'])],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
