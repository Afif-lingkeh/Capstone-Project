<?php
// app/Http/Requests/Admin/VerifyLinkRequest.php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class VerifyLinkRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('verify', $this->route('link'));
    }

    public function rules(): array
    {
        return [
            // Wajib: verifikasi tanpa bukti sumber tidak bisa dipertanggungjawabkan.
            'verification_source' => ['required', 'string', 'min:10', 'max:1000'],
        ];
    }
}
