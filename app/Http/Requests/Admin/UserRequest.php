<?php
// app/Http/Requests/Admin/UserRequest.php

namespace App\Http\Requests\Admin;

use App\Enums\Gender;
use App\Enums\Role;
use App\Enums\SantriLevel;
use App\Models\SantriProfile;
use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        $target = $this->target();

        return $target
            ? $this->user()->can('update', $target)
            : $this->user()->can('create', User::class);
    }

    public function rules(): array
    {
        $target = $this->target();
        $editingSelf = $target?->is($this->user()) ?? false;

        return [
            'name' => ['required', 'string', 'max:255'],
            'username' => ['required', 'string', 'alpha_dash', 'max:50', Rule::unique(User::class)->ignore($target?->id)],
            'email' => ['nullable', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($target?->id)],
            'gender' => ['required', Rule::enum(Gender::class)],
            'phone' => ['nullable', 'string', 'max:20'],
            'role' => [
                'required',
                Rule::enum(Role::class),
                // Super Admin tidak boleh menurunkan perannya sendiri (bisa terkunci dari sistem).
                ...($editingSelf ? [Rule::in([Role::SuperAdmin->value])] : []),
            ],
            'password' => [$target ? 'nullable' : 'required', 'confirmed', Password::defaults()],

            // Wajib hanya jika peran = santri.
            'nis' => ['nullable', 'required_if:role,santri', 'string', 'max:30', Rule::unique(SantriProfile::class, 'nis')->ignore($target?->santriProfile?->id)],
            'level' => ['nullable', 'required_if:role,santri', Rule::enum(SantriLevel::class)],
            'class_name' => ['nullable', 'required_if:role,santri', 'string', 'max:30'],
            'entry_year' => ['nullable', 'required_if:role,santri', 'integer', 'digits:4', 'max:'.(now()->year + 1)],
            'guardian_name' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'role.in' => 'Anda tidak dapat mengubah peran akun Anda sendiri.',
        ];
    }

    public function userAttributes(): array
    {
        $attributes = $this->safe()->only(['name', 'username', 'email', 'gender', 'phone']);

        if ($this->filled('password')) {
            $attributes['password'] = $this->validated('password');
        }

        return $attributes;
    }

    public function santriAttributes(): array
    {
        return $this->safe()->only(['nis', 'level', 'class_name', 'entry_year', 'guardian_name']);
    }

    public function isSantri(): bool
    {
        return $this->validated('role') === Role::Santri->value;
    }

    private function target(): ?User
    {
        $user = $this->route('user');

        return $user instanceof User ? $user : null;
    }
}
