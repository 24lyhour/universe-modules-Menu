<?php

namespace Modules\Menu\Http\Requests\Dashboard\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Modules\Menu\Models\Menu;

class MuteMenuRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'preset' => ['required', 'string', Rule::in(array_keys(Menu::mutePresets()))],
            'muted_until' => ['required_if:preset,custom', 'nullable', 'date', 'after:now', 'before:+1 year'],
            'reason' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'preset.in' => 'Pick one of the available mute durations.',
            'muted_until.required_if' => 'Pick a date and time for the custom mute.',
            'muted_until.after' => 'The end time must be in the future.',
            'muted_until.before' => 'The end time cannot be more than a year out.',
        ];
    }
}
