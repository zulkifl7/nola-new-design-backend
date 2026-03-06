<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $payload = $this->all();
        if (isset($payload['colors']) && is_array($payload['colors'])) {
            $payload['colors'] = array_values(array_map(function ($row) {
                if (is_string($row)) {
                    return ['name' => $row];
                }
                if (is_array($row)) {
                    $name = $row['name'] ?? $row['label'] ?? $row['value'] ?? null;
                    $hex = $row['hex'] ?? $row['color'] ?? null;
                    if (is_string($hex)) {
                        $hex = ltrim($hex, '#');
                        if (preg_match('/^[0-9A-Fa-f]{6}$/', $hex)) {
                            $hex = '#' . strtoupper($hex);
                        } else {
                            $hex = null;
                        }
                    }
                    return ['name' => $name, 'hex' => $hex];
                }
                return ['name' => null, 'hex' => null];
            }, $payload['colors']));
        }
        if (isset($payload['sizes']) && is_array($payload['sizes'])) {
            $payload['sizes'] = array_values(array_map(function ($row) {
                if (is_string($row)) {
                    return ['name' => $row];
                }
                if (is_array($row)) {
                    $name = $row['name'] ?? $row['label'] ?? $row['value'] ?? null;
                    return ['name' => $name];
                }
                return ['name' => null];
            }, $payload['sizes']));
        }
        if (isset($payload['materials']) && is_array($payload['materials'])) {
            $payload['materials'] = array_values(array_map(function ($row) {
                if (is_string($row)) {
                    return ['text' => $row];
                }
                if (is_array($row)) {
                    $text = $row['text'] ?? $row['label'] ?? $row['value'] ?? null;
                    return ['text' => $text];
                }
                return ['text' => null];
            }, $payload['materials']));
        }
        if (isset($payload['care']) && is_array($payload['care'])) {
            $payload['care'] = array_values(array_map(function ($row) {
                if (is_string($row)) {
                    return ['text' => $row];
                }
                if (is_array($row)) {
                    $text = $row['text'] ?? $row['label'] ?? $row['value'] ?? null;
                    return ['text' => $text];
                }
                return ['text' => null];
            }, $payload['care']));
        }
        $this->replace($payload);
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
