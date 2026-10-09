<?php

namespace App\Http\Requests;

use App\Enums\PemohonEnum;
use App\Enums\StatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ServiceRequestRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'occurred_at' => ['required', 'date'],
            'category' => ['required', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'requester' => ['required', Rule::in(PemohonEnum::values())],
            'follow_up' => ['nullable', 'string'],
            'completed_at' => ['nullable', 'date', 'after_or_equal:occurred_at'],
            'status' => ['required', Rule::in(StatusEnum::values())],
        ];
    }

    public function attributes(): array
    {
        return [
            'occurred_at' => 'Tanggal Terjadi',
            'category' => 'Kategori Permintaan',
            'title' => 'Permintaan',
            'requester' => 'Pemohon',
            'follow_up' => 'Status Permintaan',
            'completed_at' => 'Tanggal Selesai',
            'status' => 'Status',
        ];
    }
}
