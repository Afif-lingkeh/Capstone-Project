<?php
// app/Http/Requests/Admin/ProgramRequest.php

namespace App\Http\Requests\Admin;

use App\Enums\ProgramStatus;
use App\Enums\ProgramType;
use App\Models\Program;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ProgramRequest extends FormRequest
{
    public function authorize(): bool
    {
        $program = $this->route('program');

        return $program instanceof Program
            ? $this->user()->can('update', $program)
            : $this->user()->can('create', Program::class);
    }

    public function rules(): array
    {
        return [
            'partner_id' => ['required', 'integer', Rule::exists('partners', 'id')],
            'country_id' => ['required', 'integer', Rule::exists('countries', 'id')],
            'title' => ['required', 'string', 'max:255'],
            'type' => ['required', Rule::enum(ProgramType::class)],
            'summary' => ['required', 'string', 'max:500'],
            'requirements' => ['required', 'string', 'max:5000'],
            'costs' => ['required', 'string', 'max:3000'],
            'min_education' => ['nullable', 'string', 'max:100'],
            'deadline' => ['nullable', 'date'],
            'official_contact' => ['required', 'string', 'max:255'],
            'official_url' => ['nullable', 'url:http,https', 'max:255'],
            'status' => ['required', Rule::enum(ProgramStatus::class)],
            'publish' => ['boolean'],
        ];
    }

    /** Data siap simpan: checkbox "publish" diterjemahkan menjadi published_at. */
    public function programData(?Program $program = null): array
    {
        $data = collect($this->validated())->except('publish')->all();
        $data['published_at'] = $this->boolean('publish')
            ? ($program?->published_at ?? now())
            : null;

        return $data;
    }
}
