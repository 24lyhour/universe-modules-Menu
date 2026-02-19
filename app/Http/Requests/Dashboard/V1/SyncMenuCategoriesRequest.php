<?php

namespace Modules\Menu\Http\Requests\Dashboard\V1;

use Illuminate\Foundation\Http\FormRequest;

class SyncMenuCategoriesRequest extends FormRequest
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
            'category_ids' => ['required', 'array'],
            'category_ids.*' => ['required', 'integer', 'exists:menu_categories,id'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'category_ids.required' => 'Please select at least one category.',
            'category_ids.array' => 'Categories must be an array.',
            'category_ids.*.exists' => 'One or more selected categories do not exist.',
        ];
    }
}
