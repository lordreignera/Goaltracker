<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return (bool) $this->user()?->isSuperAdmin();
    }

    public function rules(): array
    {
        $user = $this->route('user');
        $assignableRoles = Role::where('name', '!=', 'Super Admin')->pluck('name')->all();

        return [
            'first_name' => ['required', 'string', 'max:100'],
            'second_name' => ['required', 'string', 'max:100'],
            'phone_number' => ['required', 'string', 'max:30', Rule::unique('users', 'phone_number')->ignore($user?->id)],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user?->id)],
            'department_id' => ['required', 'exists:departments,id'],
            'department_ids' => ['nullable', 'array'],
            'department_ids.*' => ['integer', 'exists:departments,id'],
            'section_id' => [
                'nullable',
                Rule::exists('sections', 'id')
                    ->where(fn ($query) => $query->where('department_id', $this->input('department_id'))),
            ],
            'unit_id' => [
                'nullable',
                Rule::exists('units', 'id')
                    ->where(fn ($query) => $query
                        ->where('department_id', $this->input('department_id'))
                        ->when($this->filled('section_id'), fn ($query) => $query->where('section_id', $this->input('section_id')))),
            ],
            'requested_role' => ['required', Rule::in($assignableRoles)],
            'is_active' => ['nullable', 'boolean'],
        ];
    }
}
