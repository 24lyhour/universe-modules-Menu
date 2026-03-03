<?php

namespace Modules\Menu\Http\Requests\Dashboard\V1;

use Illuminate\Foundation\Http\FormRequest;

class UpdateMenuScheduleRequest extends FormRequest
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
            'schedule_mode' => ['nullable', 'string', 'in:always,daily,weekly,date_range'],
            'schedule_days' => ['nullable', 'string'],
            'schedule_start_time' => ['nullable', 'string'],
            'schedule_end_time' => ['nullable', 'string'],
            'schedule_start_date' => ['nullable', 'date'],
            'schedule_end_date' => ['nullable', 'date', 'after_or_equal:schedule_start_date'],
            'schedule_status' => ['nullable', 'boolean'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'schedule_mode.in' => 'Invalid schedule mode. Must be one of: always, daily, weekly, date_range.',
            'schedule_end_date.after_or_equal' => 'End date must be after or equal to the start date.',
        ];
    }
}
