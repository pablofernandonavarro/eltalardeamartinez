<?php

namespace App\Livewire\Admin\Units;

use App\Models\Building;
use App\Models\Unit;
use Livewire\Component;

class Edit extends Component
{
    public Unit $unit;

    public int $buildingId = 0;

    public string $number = '';

    public ?string $floor = null;

    public float $coefficient = 1.0;

    public ?int $rooms = null;

    public ?int $terrazas = null;

    public ?float $area = null;

    public ?string $notes = null;

    public function mount(Unit $unit): void
    {
        $this->unit = $unit;
        $this->buildingId = $unit->building_id;
        $this->number = $unit->number;
        $this->floor = $unit->floor;
        $this->coefficient = (float) $unit->coefficient;
        $this->rooms = $unit->rooms;
        $this->terrazas = $unit->terrazas;
        $this->area = $unit->area ? (float) $unit->area : null;
        $this->notes = $unit->notes;
    }

    public function update(): void
    {
        $validated = $this->validate([
            'buildingId' => ['required', 'exists:buildings,id'],
            'number' => [
                'required',
                'string',
                'max:255',
                "unique:units,number,{$this->unit->id},id,building_id,{$this->buildingId}",
            ],
            'floor' => ['nullable', 'string', 'max:255'],
            'coefficient' => ['required', 'numeric', 'min:0', 'max:9999.9999'],
            'rooms' => ['nullable', 'integer', 'min:1', 'max:4'],
            'terrazas' => ['nullable', 'integer', 'min:0'],
            'area' => ['nullable', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ], [
            'buildingId.required' => 'El edificio es obligatorio.',
            'buildingId.exists' => 'El edificio seleccionado no existe.',
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
        ]);

        $this->unit->update([
            'building_id' => $validated['buildingId'],
            'number' => $validated['number'],
            'floor' => $validated['floor'],
            'coefficient' => $validated['coefficient'],
            'rooms' => $validated['rooms'],
            'terrazas' => $validated['terrazas'],
            'area' => $validated['area'],
            'notes' => $validated['notes'],
        ]);

        session()->flash('message', 'Unidad funcional actualizada correctamente.');
        $this->redirect(route('admin.units.index'));
    }

    public function render()
    {
        $buildings = Building::whereNull('deleted_at')
            ->whereHas('complex', function ($query) {
                $query->whereNull('deleted_at');
            })
            ->with('complex')
            ->orderBy('name')
            ->get();

        return view('livewire.admin.units.edit', [
            'buildings' => $buildings,
        ])->layout('components.layouts.app', ['title' => 'Editar Unidad Funcional']);
    }
}
