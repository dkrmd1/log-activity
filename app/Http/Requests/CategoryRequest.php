<?php

namespace App\Http\Requests;

use App\Models\Category;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $category = $this->route('category');
        $type = $category?->type ?? $this->input('type');

        return [
            'type' => ['required', Rule::in(array_keys(Category::TYPES))],
            'name' => [
                'required', 'string', 'max:255',
                Rule::unique('categories', 'name')->where('type', $type)->ignore($category?->id),
            ],
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'Nama',
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'Nama ini sudah ada di daftar.',
        ];
    }
}
