<?php

namespace App\Actions\Fortify;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Laravel\Fortify\Contracts\CreatesNewUsers;
use Laravel\Jetstream\Jetstream;
use Spatie\Permission\Models\Role;

class CreateNewUser implements CreatesNewUsers
{
    use PasswordValidationRules;

    /**
     * Validate and create a newly registered user.
     *
     * @param  array<string, string>  $input
     */
    public function create(array $input): User
    {
        $requestableRoles = Role::whereNotIn('name', ['Super Admin', 'Admin'])
            ->pluck('name')
            ->all() ?: ['Staff', 'Supervisor', 'Manager'];

        Validator::make($input, [
            'first_name' => ['required', 'string', 'max:100'],
            'second_name' => ['required', 'string', 'max:100'],
            'phone_number' => ['required', 'string', 'max:30', 'unique:users,phone_number'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'department_id' => ['required', 'exists:departments,id'],
            'unit_id' => ['nullable', 'exists:units,id'],
            'requested_role' => ['required', Rule::in($requestableRoles)],
            'password' => $this->passwordRules(),
            'terms' => Jetstream::hasTermsAndPrivacyPolicyFeature() ? ['accepted', 'required'] : '',
        ])->validate();

        $user = User::create([
            'first_name' => $input['first_name'],
            'second_name' => $input['second_name'],
            'name' => trim($input['first_name'].' '.$input['second_name']),
            'email' => $input['email'],
            'phone_number' => $input['phone_number'],
            'department_id' => $input['department_id'],
            'unit_id' => $input['unit_id'] ?? null,
            'role' => 'staff',
            'requested_role' => $input['requested_role'],
            'approval_status' => 'pending',
            'password' => Hash::make($input['password']),
        ]);

        return $user;
    }
}
