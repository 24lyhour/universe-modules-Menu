<?php

namespace Modules\Menu\Http\Requests\Dashboard\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateCategoryRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'menu_id' => ['nullable', 'exists:menus,id'],
            'image_url' => ['nullable', 'string'],
            'product_type' => ['nullable', 'string', 'in:phone,computer,tablet,accessory,other'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'status' => ['required', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Category name is required.',
            'name.max' => 'Category name must be less than 255 characters.',
            'description.max' => 'Description must be less than 1000 characters.',
            'menu_id.exists' => 'The selected menu does not exist.',
            'sort_order.min' => 'Sort order must be at least 0.',
        ];
    }
}
