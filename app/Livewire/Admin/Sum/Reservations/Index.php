<?php

namespace App\Livewire\Admin\Sum\Reservations;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.admin.sum.reservations.index')
            ->layout('components.layouts.app', ['title' => 'Reservas del SUM']);
    }
}
