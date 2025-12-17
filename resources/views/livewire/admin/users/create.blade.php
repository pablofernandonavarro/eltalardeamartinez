<div>
    <div class="mb-6">
        <flux:heading size="xl">Crear Usuario</flux:heading>
    </div>

    <form wire:submit="save" class="space-y-6">
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
            <flux:label>Contraseña <span class="text-red-500">*</span></flux:label>
            <flux:input type="password" wire:model="password" placeholder="Mínimo 8 caracteres" />
            <flux:error name="password" />
        </flux:field>

        <flux:field>
            <flux:label>Rol <span class="text-red-500">*</span></flux:label>
            <flux:select wire:model="role">
                <option value="{{ \App\Role::Admin->value }}">{{ \App\Role::Admin->label() }}</option>
                <option value="{{ \App\Role::Banero->value }}">{{ \App\Role::Banero->label() }}</option>
                <option value="{{ \App\Role::Propietario->value }}">{{ \App\Role::Propietario->label() }}</option>
                <option value="{{ \App\Role::Inquilino->value }}">{{ \App\Role::Inquilino->label() }}</option>
            </flux:select>
            <flux:error name="role" />
        </flux:field>

        <div class="flex gap-4">
            <flux:button type="submit" variant="primary">
                Guardar
            </flux:button>
            <flux:button href="{{ route('admin.users.index') }}" variant="ghost">
                Cancelar
            </flux:button>
        </div>
    </form>
</div>
