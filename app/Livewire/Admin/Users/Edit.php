<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use App\Role;
use Illuminate\Support\Facades\Hash;
use Livewire\Component;

class Edit extends Component
{
    public User $user;

    public string $name = '';

    public string $email = '';

    public string $password = '';

    public string $role = '';

    public function mount(User $user): void
    {
        $this->user = $user;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role?->value ?? '';
    }

    public function update(): void
    {
        $validated = $this->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$this->user->id,
            'password' => 'nullable|string|min:8',
            'role' => 'required|in:'.implode(',', array_column(Role::cases(), 'value')),
        ], [
            'name.required' => 'El nombre es obligatorio.',
            'name.max' => 'El nombre no puede exceder 255 caracteres.',
            'email.required' => 'El email es obligatorio.',
            'email.email' => 'El email debe ser válido.',
            'email.unique' => 'Este email ya está registrado.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'role.required' => 'El rol es obligatorio.',
        ]);

        $data = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'role' => Role::from($validated['role']),
        ];

        if (! empty($validated['password'])) {
            $data['password'] = Hash::make($validated['password']);
        }

        $this->user->update($data);

        session()->flash('message', 'Usuario actualizado correctamente.');
        $this->redirect(route('admin.users.index'));
    }

    public function render()
    {
        return view('livewire.admin.users.edit')
            ->layout('components.layouts.app', ['title' => 'Editar Usuario']);
    }
}
