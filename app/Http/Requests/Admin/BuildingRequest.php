<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class BuildingRequest extends FormRequest
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
        $buildingId = $this->route('building')?->id;

        return [
            'complex_id' => ['required', 'exists:complexes,id'],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'floors' => ['nullable', 'integer', 'min:1'],
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
            'complex_id.required' => 'El complejo es obligatorio.',
            'complex_id.exists' => 'El complejo seleccionado no existe.',
            'name.required' => 'El nombre del edificio es obligatorio.',
            'name.max' => 'El nombre no puede exceder 255 caracteres.',
            'floors.integer' => 'El número de pisos debe ser un número entero.',
            'floors.min' => 'El número de pisos debe ser al menos 1.',
        ];
    }
}
