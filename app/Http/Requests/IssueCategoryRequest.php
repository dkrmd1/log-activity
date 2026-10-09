<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class IssueCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $categoryId = $this->route('issue_category')?->id;

        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('issue_categories', 'name')->ignore($categoryId)],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Nama Kategori',
        ];
    }
}
