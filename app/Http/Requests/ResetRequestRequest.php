<?php

namespace App\Http\Requests;

use App\Enums\JenisPermohonanEnum;
use App\Enums\StatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ResetRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'requested_at' => ['required', 'date'],
            'account_user_id' => ['required', 'string', 'max:255'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255'],
            'types' => ['required', 'array', 'min:1'],
            'types.*' => [Rule::in(JenisPermohonanEnum::values())],
            'notes' => ['nullable', 'string'],
            'status' => ['required', Rule::in(StatusEnum::values())],
        ];
    }

    public function attributes(): array
    {
        return [
            'requested_at' => 'Tanggal Permohonan',
            'account_user_id' => 'User ID',
            'name' => 'Nama',
            'email' => 'Email',
            'types' => 'Jenis Permohonan',
            'notes' => 'Keterangan',
            'status' => 'Status',
        ];
    }

    public function messages(): array
    {
        return [
            'types.required' => 'Pilih minimal 1 jenis permohonan.',
            'types.min' => 'Pilih minimal 1 jenis permohonan.',
        ];
    }
}
