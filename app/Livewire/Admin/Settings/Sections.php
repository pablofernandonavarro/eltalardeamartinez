<?php

namespace App\Livewire\Admin\Settings;

use App\Models\SiteSetting;
use Livewire\Component;

class Sections extends Component
{
    public array $sections = [];

    public function mount(): void
    {
        $this->loadSections();
    }

    public function toggle(string $key): void
    {
        if (! array_key_exists($key, SiteSetting::SECTIONS)) {
            return;
        }

        $current = SiteSetting::get($key, true);
        SiteSetting::set($key, ! $current);
        $this->loadSections();

        $label = SiteSetting::SECTIONS[$key]['label'];
        $state = ! $current ? 'habilitada' : 'deshabilitada';
        session()->flash('message', "Sección \"{$label}\" {$state}.");
    }

    private function loadSections(): void
    {
        $this->sections = SiteSetting::allSections();
    }

    public function render()
    {
        return view('livewire.admin.settings.sections')
            ->layout('components.layouts.app', ['title' => 'Secciones del Portal']);
    }
}
