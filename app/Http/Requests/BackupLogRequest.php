<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BackupLogRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'jenis' => ['required', 'string', 'max:255'],
            'backup_date' => ['required', 'date'],
            'backup_time' => ['required', 'date_format:H:i'],
            'notes' => ['nullable', 'string'],
        ];
    }

    public function attributes(): array
    {
        return [
            'jenis' => 'Jenis Backup',
            'backup_date' => 'Tanggal',
            'backup_time' => 'Jam',
            'notes' => 'Keterangan',
        ];
    }
}
