<?php
// app/Http/Requests/Admin/InstitutionRequest.php

namespace App\Http\Requests\Admin;

use App\Enums\InstitutionType;
use App\Models\Institution;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InstitutionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $institution = $this->route('institution');

        return $institution instanceof Institution
            ? $this->user()->can('update', $institution)
            : $this->user()->can('create', Institution::class);
    }

    public function rules(): array
    {
        $institution = $this->route('institution');

        return [
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique(Institution::class)
                    ->where('country_id', $this->input('country_id'))
                    ->ignore($institution instanceof Institution ? $institution->id : null),
            ],
            'type' => ['required', Rule::enum(InstitutionType::class)],
            'country_id' => ['required', 'integer', Rule::exists('countries', 'id')],
            'city' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:2000'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Institusi dengan nama ini sudah ada di negara yang sama.',
        ];
    }
}
