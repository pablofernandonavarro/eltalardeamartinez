<?php

namespace App\Livewire\Admin\Buildings;

use App\Models\Building;
use App\Models\Complex;
use Livewire\Component;

class Create extends Component
{
    public int $complexId = 0;

    public string $name = '';

    public ?string $address = null;

    public ?int $floors = null;

    public ?string $notes = null;

    public function save(): void
    {
        $validated = $this->validate([
            'complexId' => 'required|exists:complexes,id',
            'name' => 'required|string|max:255',
            'address' => 'nullable|string|max:255',
            'floors' => 'nullable|integer|min:1',
            'notes' => 'nullable|string',
        ], [
            'complexId.required' => 'El complejo es obligatorio.',
            'complexId.exists' => 'El complejo seleccionado no existe.',
            'name.required' => 'El nombre del edificio es obligatorio.',
            'name.max' => 'El nombre no puede exceder 255 caracteres.',
            'floors.integer' => 'El número de pisos debe ser un número entero.',
            'floors.min' => 'El número de pisos debe ser al menos 1.',
        ]);

        Building::create([
            'complex_id' => $validated['complexId'],
            'name' => $validated['name'],
            'address' => $validated['address'],
            'floors' => $validated['floors'],
            'notes' => $validated['notes'],
        ]);

        session()->flash('message', 'Edificio creado correctamente.');
        $this->redirect(route('admin.buildings.index'));
    }

    public function render()
    {
        $complexes = Complex::all();

        return view('livewire.admin.buildings.create', [
            'complexes' => $complexes,
        ])->layout('components.layouts.app', ['title' => 'Crear Edificio']);
    }
}
