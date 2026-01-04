<?php

namespace App\Livewire\Resident;

use App\Models\Resident;
use App\Models\User;
use Illuminate\Support\Str;
use Livewire\Component;

class MyPoolQr extends Component
{
    public ?Resident $resident = null;
    public ?User $user = null;
    public bool $useUserQr = false;

    public function mount(): void
    {
        $this->user = auth()->user();

        // Buscar el residente asociado a este usuario autenticado
        $this->resident = Resident::query()
            ->where('auth_user_id', $this->user->id)
            ->active()
            ->first();

        // Si no es residente, usar QR del usuario directamente
        if (!$this->resident) {
            $this->useUserQr = true;
            
            // Generar QR para el usuario si no lo tiene
            if (!$this->user->qr_token) {
                $this->user->qr_token = (string) Str::uuid();
                $this->user->save();
            }
        } else {
            // Si el residente existe y puede tener QR, generarlo si no lo tiene
            if ($this->resident->canHavePersonalQr() && ! $this->resident->qr_token) {
                $this->resident->generateQrToken();
                $this->resident->refresh();
            }
        }
    }

    public function regenerateQr(): void
    {
        if ($this->useUserQr) {
            // Regenerar QR del usuario
            $this->user->qr_token = (string) Str::uuid();
            $this->user->save();
            $this->dispatch('resident-qr-updated', token: $this->user->qr_token);
        } else {
            // Regenerar QR del residente
            if (! $this->resident || ! $this->resident->canHavePersonalQr()) {
                $this->addError('error', 'No se puede regenerar el QR.');
                return;
            }

            $this->resident->qr_token = (string) Str::uuid();
            $this->resident->save();
            $this->resident->refresh();
            $this->dispatch('resident-qr-updated', token: $this->resident->qr_token);
        }

        session()->flash('message', 'QR regenerado exitosamente.');
    }

    public function render()
    {
        // Obtener el token correcto según el tipo
        $qrToken = $this->useUserQr ? $this->user->qr_token : $this->resident?->qr_token;
        
        return view('livewire.resident.my-pool-qr', [
            'resident' => $this->resident,
            'user' => $this->user,
            'useUserQr' => $this->useUserQr,
            'qrToken' => $qrToken,
        ])->layout('components.layouts.resident', ['title' => 'Mi QR de Pileta']);
    }
}
