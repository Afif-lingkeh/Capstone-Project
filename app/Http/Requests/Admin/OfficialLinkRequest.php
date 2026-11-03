<?php
// app/Http/Requests/Admin/OfficialLinkRequest.php

namespace App\Http\Requests\Admin;

use App\Models\OfficialLink;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class OfficialLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        $link = $this->route('link');

        return $link instanceof OfficialLink
            ? $this->user()->can('update', $link)
            : $this->user()->can('create', OfficialLink::class);
    }

    protected function prepareForValidation(): void
    {
        $this->merge(['url' => trim((string) $this->input('url'))]);
    }

    public function rules(): array
    {
        $link = $this->route('link');

        return [
            'institution_id' => ['required', 'integer', Rule::exists('institutions', 'id')],
            'label' => ['required', 'string', 'max:100'],
            // Hanya http/https: menolak javascript:, data:, dll.
            'url' => [
                'required', 'string', 'max:500', 'url:http,https',
                Rule::unique(OfficialLink::class)->ignore($link instanceof OfficialLink ? $link->id : null),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'url.unique' => 'URL ini sudah terdaftar di direktori.',
        ];
    }
}
