<?php

namespace App\Livewire\Admin\Sum\Payments;

use Livewire\Component;

class Index extends Component
{
    public function render()
    {
        return view('livewire.admin.sum.payments.index')
            ->layout('components.layouts.app', ['title' => 'Pagos y Facturas del SUM']);
    }
}
