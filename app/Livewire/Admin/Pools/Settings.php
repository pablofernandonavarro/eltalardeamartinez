<?php

namespace App\Livewire\Admin\Pools;

use App\Models\PoolSetting;
use Livewire\Component;

class Settings extends Component
{
    public int $maxGuestsWeekday = 4;
    public int $maxGuestsWeekend = 2;
    public int $maxGuestsMonth = 5;
    public int $maxGuestsWeekendMonth = 3;
    public int $maxEntriesPerDay = 0;
    public bool $allowExtraPayment = false;

    public function mount(): void
    {
        $this->loadSettings();
    }

    protected function loadSettings(): void
    {
        $this->maxGuestsWeekday = PoolSetting::get('max_guests_weekday', 4);
        $this->maxGuestsWeekend = PoolSetting::get('max_guests_weekend', 2);
        $this->maxGuestsMonth = PoolSetting::get('max_guests_month', 5);
        $this->maxGuestsWeekendMonth = PoolSetting::get('max_guests_weekend_month', 3);
        $this->maxEntriesPerDay = PoolSetting::get('max_entries_per_day', 0);
        $this->allowExtraPayment = PoolSetting::get('allow_extra_payment', false);
    }

    public function save(): void
    {
        $this->validate([
            'maxGuestsWeekday' => 'required|integer|min:0|max:20',
            'maxGuestsWeekend' => 'required|integer|min:0|max:20',
            'maxGuestsMonth' => 'required|integer|min:0|max:50',
            'maxGuestsWeekendMonth' => 'required|integer|min:0|max:50',
            'maxEntriesPerDay' => 'required|integer|min:0|max:20',
            'allowExtraPayment' => 'boolean',
        ], [
            'maxGuestsWeekday.required' => 'El límite de días de semana es obligatorio.',
            'maxGuestsWeekday.min' => 'El límite debe ser al menos 0.',
            'maxGuestsWeekday.max' => 'El límite no puede exceder 20.',
            'maxGuestsWeekend.required' => 'El límite de fin de semana es obligatorio.',
            'maxGuestsWeekend.min' => 'El límite debe ser al menos 0.',
            'maxGuestsWeekend.max' => 'El límite no puede exceder 20.',
            'maxGuestsMonth.required' => 'El límite mensual es obligatorio.',
            'maxGuestsMonth.min' => 'El límite debe ser al menos 0.',
            'maxGuestsMonth.max' => 'El límite no puede exceder 50.',
            'maxGuestsWeekendMonth.required' => 'El límite mensual de fin de semana es obligatorio.',
            'maxGuestsWeekendMonth.min' => 'El límite debe ser al menos 0.',
            'maxGuestsWeekendMonth.max' => 'El límite no puede exceder 50.',
            'maxEntriesPerDay.required' => 'El límite de ingresos diarios es obligatorio.',
            'maxEntriesPerDay.min' => 'El límite debe ser al menos 0.',
            'maxEntriesPerDay.max' => 'El límite no puede exceder 20.',
        ]);

        PoolSetting::set('max_guests_weekday', $this->maxGuestsWeekday);
        PoolSetting::set('max_guests_weekend', $this->maxGuestsWeekend);
        PoolSetting::set('max_guests_month', $this->maxGuestsMonth);
        PoolSetting::set('max_guests_weekend_month', $this->maxGuestsWeekendMonth);
        PoolSetting::set('max_entries_per_day', $this->maxEntriesPerDay);
        PoolSetting::set('allow_extra_payment', $this->allowExtraPayment ? 'true' : 'false');

        // Limpiar caché para que los cambios se apliquen inmediatamente
        PoolSetting::clearCache();

        session()->flash('message', '✅ Configuración guardada correctamente. Los cambios están activos inmediatamente.');
    }

    public function resetToDefault(): void
    {
        $this->maxGuestsWeekday = 4;
        $this->maxGuestsWeekend = 2;
        $this->maxGuestsMonth = 5;
        $this->maxGuestsWeekendMonth = 3;
        $this->maxEntriesPerDay = 0;
        $this->allowExtraPayment = false;

        session()->flash('info', 'Valores restaurados a los predeterminados. Haz clic en "Guardar Configuración" para aplicarlos.');
    }

    public function render()
    {
        $allSettings = PoolSetting::all();

        return view('livewire.admin.pools.settings', [
            'allSettings' => $allSettings,
        ])->layout('components.layouts.app', ['title' => 'Configuración de Pileta']);
    }
}
