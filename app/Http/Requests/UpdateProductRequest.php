<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('id');
        return [
            'category_id' => ['sometimes', 'exists:categories,id'],
            'code' => ['sometimes', 'string', 'max:50', \Illuminate\Validation\Rule::unique('products', 'code')->ignore($id)],
            'name' => ['sometimes', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:180', \Illuminate\Validation\Rule::unique('products', 'slug')->ignore($id)],
            'description' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
            'lead_time_days' => ['nullable', 'integer', 'min:0', 'max:365'],
            'featured' => ['boolean'],
            'status' => ['in:draft,active,archived'],
            'seo_title' => ['nullable', 'string', 'max:60'],
            'seo_description' => ['nullable', 'string', 'max:160'],
            'colors' => ['array'],
            'colors.*.name' => ['required', 'string', 'max:50'],
            'colors.*.hex' => ['nullable', 'regex:/^#[0-9A-Fa-f]{6}$/'],
            'sizes' => ['array'],
            'sizes.*.name' => ['required', 'string', 'max:20'],
            'materials' => ['array'],
            'materials.*.text' => ['required', 'string', 'max:255'],
            'care' => ['array'],
            'care.*.text' => ['required', 'string', 'max:255'],
        ];
    }
}
