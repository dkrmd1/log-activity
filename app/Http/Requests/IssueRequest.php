<?php

namespace App\Http\Requests;

use App\Enums\PemohonEnum;
use App\Enums\StatusEnum;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IssueRequest extends FormRequest
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
            'description' => ['nullable', 'string'],
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
            'category' => 'Kategori Isu',
            'title' => 'Isu',
            'description' => 'Deskripsi',
            'requester' => 'Pemohon',
            'follow_up' => 'Tindak Lanjut Isu',
            'completed_at' => 'Tanggal Selesai',
            'status' => 'Status',
        ];
    }
}
