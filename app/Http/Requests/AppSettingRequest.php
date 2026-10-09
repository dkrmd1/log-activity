<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AppSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'app_name' => ['required', 'string', 'max:255'],
            'logo' => ['nullable', 'image', 'max:1024'], // max 1MB
            'favicon' => ['nullable', 'mimes:ico,png,jpg,jpeg,svg', 'max:512'], // max 512KB
            'report_logo' => ['nullable', 'mimes:png,jpg,jpeg', 'max:1024'], // logo khusus PDF laporan, max 1MB
        ];
    }

    public function attributes(): array
    {
        return [
            'app_name' => 'Nama Aplikasi',
            'logo' => 'Logo',
            'favicon' => 'Favicon',
            'report_logo' => 'Logo Laporan PDF',
        ];
    }
}