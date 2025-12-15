<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UnitUserRequest extends FormRequest
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
        return [
            'unit_id' => ['required', 'exists:units,id'],
            'user_id' => ['required', 'exists:users,id'],
            'is_responsible' => ['boolean'],
            'started_at' => ['required', 'date'],
            'ended_at' => ['nullable', 'date', 'after:started_at'],
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
            'unit_id.required' => 'La unidad funcional es obligatoria.',
            'unit_id.exists' => 'La unidad funcional seleccionada no existe.',
            'user_id.required' => 'El usuario es obligatorio.',
            'user_id.exists' => 'El usuario seleccionado no existe.',
            'started_at.required' => 'La fecha de inicio es obligatoria.',
            'started_at.date' => 'La fecha de inicio debe ser una fecha válida.',
            'ended_at.date' => 'La fecha de fin debe ser una fecha válida.',
            'ended_at.after' => 'La fecha de fin debe ser posterior a la fecha de inicio.',
        ];
    }
}
