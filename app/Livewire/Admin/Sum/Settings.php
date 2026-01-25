<?php

namespace App\Livewire\Admin\Sum;

use Livewire\Component;

class Settings extends Component
{
    public int $pricePerHour = 500;
    public string $openTime = '09:00';
    public string $closeTime = '23:00';
    public int $maxDaysAdvance = 30;
    public int $minHoursNotice = 24;
    public bool $requiresApproval = false;

    public function mount(): void
    {
        // TODO: Cargar desde base de datos
    }

    public function save(): void
    {
        $this->validate([
            'pricePerHour' => 'required|integer|min:0',
            'openTime' => 'required',
            'closeTime' => 'required',
            'maxDaysAdvance' => 'required|integer|min:1|max:90',
            'minHoursNotice' => 'required|integer|min:0|max:168',
        ]);

        // TODO: Guardar en base de datos

        session()->flash('message', '✅ Configuración guardada correctamente.');
    }

    public function render()
    {
        return view('livewire.admin.sum.settings')
            ->layout('components.layouts.app', ['title' => 'Configuración del SUM']);
    }
}
