<?php

namespace Modules\Menu\Http\Requests\Dashboard\V1;

use Illuminate\Foundation\Http\FormRequest;

class SyncCategoryProductsRequest extends FormRequest
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
            'products' => ['nullable', 'array'],
            'products.*.id' => ['required', 'exists:products,id'],
            'products.*.price_override' => ['nullable', 'numeric', 'min:0'],
            'products.*.sort_order' => ['nullable', 'integer', 'min:0'],
            'products.*.is_available' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'products.*.id.required' => 'Product ID is required.',
            'products.*.id.exists' => 'One or more selected products do not exist.',
            'products.*.price_override.numeric' => 'Price override must be a number.',
            'products.*.price_override.min' => 'Price override must be at least 0.',
            'products.*.sort_order.integer' => 'Sort order must be an integer.',
            'products.*.sort_order.min' => 'Sort order must be at least 0.',
        ];
    }
}
