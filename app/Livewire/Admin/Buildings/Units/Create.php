<?php

namespace App\Livewire\Admin\Buildings\Units;

use App\Models\Building;
use App\Models\Unit;
use Livewire\Component;

class Create extends Component
{
    public Building $building;

    public string $number = '';

    public ?string $floor = null;

    public float $coefficient = 1.0;

    public ?int $rooms = null;

    public ?int $terrazas = null;

    public ?float $area = null;

    public ?int $max_residents = null;

    public ?string $notes = null;

    public function mount(Building $building): void
    {
        $this->building = $building;
    }

    public function save(): void
    {
        $validated = $this->validate([
            'number' => [
                'required',
                'string',
                'max:255',
                "unique:units,number,NULL,id,building_id,{$this->building->id}",
            ],
            'floor' => ['nullable', 'string', 'max:255'],
            'coefficient' => ['required', 'numeric', 'min:0', 'max:9999.9999'],
            'rooms' => ['nullable', 'integer', 'min:1', 'max:4'],
            'terrazas' => ['nullable', 'integer', 'min:0'],
            'area' => ['nullable', 'numeric', 'min:0'],
            'max_residents' => ['nullable', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
        ], [
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
            'max_residents.integer' => 'El límite de habitantes debe ser un número entero.',
            'max_residents.min' => 'El límite de habitantes debe ser al menos 1.',
        ]);

        Unit::create([
            'building_id' => $this->building->id,
            'number' => $validated['number'],
            'floor' => $validated['floor'],
            'coefficient' => $validated['coefficient'],
            'rooms' => $validated['rooms'],
            'terrazas' => $validated['terrazas'],
            'area' => $validated['area'],
            'max_residents' => $validated['max_residents'],
            'notes' => $validated['notes'],
        ]);

        session()->flash('message', 'Unidad funcional creada correctamente.');
        $this->redirect(route('admin.buildings.units.index', $this->building));
    }

    public function render()
    {
        return view('livewire.admin.buildings.units.create')
            ->layout('components.layouts.app', ['title' => "Crear Unidad Funcional - {$this->building->name}"]);
    }
}
