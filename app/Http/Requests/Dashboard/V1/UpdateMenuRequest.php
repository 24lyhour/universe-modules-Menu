<?php

namespace Modules\Menu\Http\Requests\Dashboard\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMenuRequest extends FormRequest
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
            'image_url' => ['nullable', 'string'],
            'status' => ['required', 'boolean'],
            'schedule_mode' => ['nullable', 'string'],
            'schedule_days' => ['nullable', 'string'],
            'schedule_start_time' => ['nullable', 'string'],
            'schedule_end_time' => ['nullable', 'string'],
            'schedule_start_date' => ['nullable', 'date'],
            'schedule_end_date' => ['nullable', 'date'],
            'schedule_status' => ['nullable', 'boolean'],
        ];
    }
}
