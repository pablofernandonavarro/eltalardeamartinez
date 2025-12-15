<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class UnitRequest extends FormRequest
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
        $unitId = $this->route('unit')?->id;
        $buildingId = $this->input('building_id');

        return [
            'building_id' => ['required', 'exists:buildings,id'],
            'number' => [
                'required',
                'string',
                'max:255',
                "unique:units,number,{$unitId},id,building_id,{$buildingId}",
            ],
            'floor' => ['nullable', 'string', 'max:255'],
            'coefficient' => ['required', 'numeric', 'min:0', 'max:9999.9999'],
            'rooms' => ['nullable', 'integer', 'min:1', 'max:4'],
            'terrazas' => ['nullable', 'integer', 'min:0'],
            'area' => ['nullable', 'numeric', 'min:0'],
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
            'building_id.required' => 'El edificio es obligatorio.',
            'building_id.exists' => 'El edificio seleccionado no existe.',
            'number.required' => 'El número de unidad es obligatorio.',
            'number.unique' => 'Ya existe una unidad con este número en este edificio.',
            'coefficient.required' => 'El coeficiente es obligatorio.',
            'coefficient.numeric' => 'El coeficiente debe ser un número.',
            'coefficient.min' => 'El coeficiente debe ser mayor o igual a 0.',
            'coefficient.max' => 'El coeficiente no puede exceder 9999.9999.',
            'rooms.integer' => 'La cantidad de ambientes debe ser un número entero.',
            'rooms.min' => 'La cantidad de ambientes debe ser al menos 1.',
            'rooms.max' => 'La cantidad de ambientes no puede exceder 4.',
            'terrazas.integer' => 'La cantidad de terrazas debe ser un número entero.',
            'terrazas.min' => 'La cantidad de terrazas debe ser mayor o igual a 0.',
            'area.numeric' => 'El área debe ser un número.',
            'area.min' => 'El área debe ser mayor o igual a 0.',
        ];
    }
}
