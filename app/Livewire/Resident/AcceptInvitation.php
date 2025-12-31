<?php

namespace App\Livewire\Resident;

use App\Role;
use App\Models\Resident;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;
use Livewire\Component;

class AcceptInvitation extends Component
{
    public ?Resident $resident = null;

    public ?string $token = null;

    #[Validate('required|email')]
    public string $email = '';

    #[Validate('required|min:8')]
    public string $password = '';

    #[Validate('required|same:password')]
    public string $password_confirmation = '';

    public bool $tokenValid = false;

    public function mount(string $token): void
    {
        $this->token = $token;

        $this->resident = Resident::with('unit')
            ->where('invitation_token', $token)
            ->whereNull('auth_user_id')
            ->active()
            ->first();

        if ($this->resident) {
            $this->tokenValid = true;
            $this->email = $this->resident->email ?? '';
        }
    }

    public function register(): void
    {
        if (! $this->tokenValid || ! $this->resident) {
            $this->addError('token', 'Token de invitación inválido.');

            return;
        }

        $this->validate();

        // Verificar que no exista ya un usuario con ese email
        if (User::where('email', $this->email)->exists()) {
            $this->addError('email', 'Ya existe una cuenta con este email.');

            return;
        }

        // Crear usuario
        $user = User::create([
            'name' => $this->resident->name,
            'email' => $this->email,
            'password' => Hash::make($this->password),
            'role' => Role::Inquilino,
            'approved_at' => now(),
        ]);

        // Vincular residente con usuario
        $this->resident->auth_user_id = $user->id;
        $this->resident->email = $this->email;
        $this->resident->invitation_token = null; // Invalidar token
        $this->resident->save();

        // Generar QR automáticamente
        $this->resident->generateQrToken();

        // Auto-login
        Auth::login($user);

        session()->flash('message', '¡Cuenta creada exitosamente! Tu QR personal ya está disponible.');

        $this->redirect(route('resident.pools.my-qr'), navigate: true);
    }

    #[Layout('components.layouts.auth')]
    public function render()
    {
        return view('livewire.resident.accept-invitation');
    }
}
