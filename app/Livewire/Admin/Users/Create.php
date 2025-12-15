<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use App\Role;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class Create extends Component
{
    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $role = Role::Propietario->value;

    public function save(): void
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:'.implode(',', array_column(Role::cases(), 'value')),
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no puede exceder 255 caracteres.',
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email debe ser válido.',
            'email.unique' => 'Este email ya está registrado.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'role.required' => 'El rol es obligatorio.',
        ]);

        User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'role' => Role::from($validated['role']),
            'approved_at' => now(), // Users created by admin are automatically approved
        ]);

        session()->flash('message', 'Usuario creado correctamente.');
        $this->redirect(route('admin.users.index'));
    }

    public function render()
    {
        return view('livewire.admin.users.create')
            ->layout('components.layouts.app', ['title' => 'Crear Usuario']);
    }
}
