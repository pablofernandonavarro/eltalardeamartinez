<?php

namespace App\Livewire\Resident;

use App\Models\Resident;
use Illuminate\Support\Str;
use Livewire\Component;

class MyPoolQr extends Component
{
    public ?Resident $resident = null;

    public function mount(): void
    {
        $user = auth()->user();

        // Buscar el residente asociado a este usuario autenticado
        $this->resident = Resident::query()
            ->where('auth_user_id', $user->id)
            ->active()
            ->first();

        // Si el residente existe y puede tener QR, generarlo si no lo tiene
        if ($this->resident && $this->resident->canHavePersonalQr() && ! $this->resident->qr_token) {
            $this->resident->generateQrToken();
            $this->resident->refresh();
        }
    }

    public function regenerateQr(): void
    {
        if (! $this->resident || ! $this->resident->canHavePersonalQr()) {
            $this->addError('error', 'No se puede regenerar el QR.');

            return;
        }

        $this->resident->qr_token = (string) Str::uuid();
        $this->resident->save();
        $this->resident->refresh();

        $this->dispatch('resident-qr-updated', token: $this->resident->qr_token);

        session()->flash('message', 'QR regenerado exitosamente.');
    }

    public function render()
    {
        return view('livewire.resident.my-pool-qr', [
            'resident' => $this->resident,
        ])->layout('components.layouts.resident', ['title' => 'Mi QR de Pileta']);
    }
}
