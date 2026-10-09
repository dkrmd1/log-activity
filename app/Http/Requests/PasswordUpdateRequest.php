<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class PasswordUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ];
    }

    public function attributes(): array
    {
        return [
            'current_password' => 'Password saat ini',
            'password' => 'Password baru',
        ];
    }

    public function messages(): array
    {
        return [
            'current_password.current_password' => 'Password saat ini yang Anda masukkan salah.',
        ];
    }
}
