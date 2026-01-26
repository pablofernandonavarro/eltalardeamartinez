<?php

namespace App\Livewire\Admin\Sum;

use App\Models\SumSetting;
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
        $this->pricePerHour = SumSetting::get('price_per_hour', 500);
        $this->openTime = SumSetting::get('open_time', '09:00');
        $this->closeTime = SumSetting::get('close_time', '23:00');
        $this->maxDaysAdvance = SumSetting::get('max_days_advance', 30);
        $this->minHoursNotice = SumSetting::get('min_hours_notice', 24);
        $this->requiresApproval = SumSetting::get('requires_approval', false);
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

        SumSetting::set('price_per_hour', $this->pricePerHour);
        SumSetting::set('open_time', $this->openTime);
        SumSetting::set('close_time', $this->closeTime);
        SumSetting::set('max_days_advance', $this->maxDaysAdvance);
        SumSetting::set('min_hours_notice', $this->minHoursNotice);
        SumSetting::set('requires_approval', $this->requiresApproval ? 'true' : 'false');

        SumSetting::clearCache();

        session()->flash('message', 'Configuracion guardada correctamente.');
    }

    public function render()
    {
        return view('livewire.admin.sum.settings')
            ->layout('components.layouts.app', ['title' => 'Configuracion del SUM']);
    }
}
