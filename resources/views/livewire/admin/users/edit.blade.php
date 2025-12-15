<div>
    <div class="mb-6">
        <flux:heading size="xl">Editar Usuario</flux:heading>
    </div>

    @if(session('message'))
        <flux:callout color="green" class="mb-4">
            {{ session('message') }}
        </flux:callout>
    @endif

    <form wire:submit="update" class="space-y-6">
        <flux:field>
            <flux:label>Nombre <span class="text-red-500">*</span></flux:label>
            <flux:input wire:model="name" placeholder="Nombre completo" />
            <flux:error name="name" />
        </flux:field>

        <flux:field>
            <flux:label>Email <span class="text-red-500">*</span></flux:label>
            <flux:input type="email" wire:model="email" placeholder="email@example.com" />
            <flux:error name="email" />
        </flux:field>

        <flux:field>
            <flux:label>Contraseña</flux:label>
            <flux:input type="password" wire:model="password" placeholder="Dejar en blanco para mantener la actual" />
            <flux:text class="text-sm text-gray-500">Dejar en blanco si no desea cambiar la contraseña</flux:text>
            <flux:error name="password" />
        </flux:field>

        <flux:field>
            <flux:label>Rol <span class="text-red-500">*</span></flux:label>
            <flux:select wire:model="role" placeholder="Seleccione un rol">
                <option value="">Seleccione un rol</option>
                <option value="{{ \App\Role::Admin->value }}">{{ \App\Role::Admin->label() }}</option>
                <option value="{{ \App\Role::Propietario->value }}">{{ \App\Role::Propietario->label() }}</option>
                <option value="{{ \App\Role::Inquilino->value }}">{{ \App\Role::Inquilino->label() }}</option>
            </flux:select>
            <flux:error name="role" />
            @if(!$user->role)
                <flux:description class="text-yellow-600">Este usuario no tiene rol asignado. Debe asignar un rol para que pueda acceder al sistema.</flux:description>
            @endif
        </flux:field>

        <div class="flex gap-4">
            <flux:button type="submit" variant="primary">
                Actualizar
            </flux:button>
            <flux:button href="{{ route('admin.users.index') }}" variant="ghost">
                Cancelar
            </flux:button>
        </div>
    </form>
</div>
