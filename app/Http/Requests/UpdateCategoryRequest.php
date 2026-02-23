<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');
        return [
            'name' => ['sometimes', 'string', 'max:100'],
            'slug' => ['nullable', 'string', 'max:120', \Illuminate\Validation\Rule::unique('categories', 'slug')->ignore($id)],
        ];
    }
}
