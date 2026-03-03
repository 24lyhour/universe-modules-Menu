<?php

namespace Modules\Menu\Http\Requests\Dashboard\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMenuTypeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'image_url' => ['nullable', 'string'],
            'outlet_id' => ['required', 'integer', 'exists:outlets,id'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'boolean'],
        ];
    }
}
