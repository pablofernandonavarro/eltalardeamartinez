<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RuleRequest extends FormRequest
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
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $ruleId = $this->route('rule') ? $this->route('rule')->id : null;

        return [
            'type' => ['required', 'string', Rule::in(['unit_occupancy', 'pool_weekly_guests', 'pool_monthly_guests'])],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'conditions' => ['nullable', 'array'],
            'limits' => ['required', 'array'],
            'is_active' => ['boolean'],
            'valid_from' => ['nullable', 'date'],
            'valid_to' => ['nullable', 'date', 'after_or_equal:valid_from'],
            'priority' => ['required', 'integer', 'min:0', 'max:100'],
            'notes' => ['nullable', 'string'],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'type.required' => 'El tipo de regla es obligatorio.',
            'type.in' => 'El tipo de regla no es válido.',
            'name.required' => 'El nombre de la regla es obligatorio.',
            'name.max' => 'El nombre no puede exceder 255 caracteres.',
            'limits.required' => 'Los límites son obligatorios.',
            'limits.array' => 'Los límites deben ser un array.',
            'priority.required' => 'La prioridad es obligatoria.',
            'priority.integer' => 'La prioridad debe ser un número entero.',
            'priority.min' => 'La prioridad debe ser al menos 0.',
            'priority.max' => 'La prioridad no puede exceder 100.',
            'valid_to.after_or_equal' => 'La fecha de fin debe ser posterior o igual a la fecha de inicio.',
        ];
    }
}
