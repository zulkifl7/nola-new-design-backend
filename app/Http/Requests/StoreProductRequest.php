<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'category_id' => ['required', 'exists:categories,id'],
            'code' => ['required', 'string', 'max:50', 'unique:products,code'],
            'name' => ['required', 'string', 'max:150'],
            'slug' => ['nullable', 'string', 'max:180', 'unique:products,slug'],
            'description' => ['nullable', 'string'],
            'price' => ['nullable', 'numeric', 'min:0'],
            'currency' => ['nullable', 'string', 'size:3'],
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
            'images' => ['array'],
            'images.*' => ['file', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }
}
