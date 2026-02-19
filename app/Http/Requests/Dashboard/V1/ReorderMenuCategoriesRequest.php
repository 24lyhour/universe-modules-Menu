<?php

namespace Modules\Menu\Http\Requests\Dashboard\V1;

use Illuminate\Foundation\Http\FormRequest;

class ReorderMenuCategoriesRequest extends FormRequest
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
            'categories' => ['required', 'array'],
            'categories.*.id' => ['required', 'integer', 'exists:menu_categories,id'],
            'categories.*.sort_order' => ['required', 'integer', 'min:0'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'categories.required' => 'Categories are required.',
            'categories.*.id.required' => 'Category ID is required.',
            'categories.*.id.exists' => 'One or more categories do not exist.',
            'categories.*.sort_order.required' => 'Sort order is required.',
            'categories.*.sort_order.min' => 'Sort order must be at least 0.',
        ];
    }
}
