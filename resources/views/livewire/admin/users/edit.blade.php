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
                <option value="{{ \App\Role::Banero->value }}">{{ \App\Role::Banero->label() }}</option>
                <option value="{{ \App\Role::Propietario->value }}">{{ \App\Role::Propietario->label() }}</option>
                <option value="{{ \App\Role::Inquilino->value }}">{{ \App\Role::Inquilino->label() }}</option>
            </flux:select>
            <flux:error name="role" />
            @if(!$user->role)
                <flux:description class="text-yellow-600">Este usuario no tiene rol asignado. Debe asignar un rol para que pueda acceder al sistema.</flux:description>
            @endif
        </flux:field>

        {{-- Unidades ya asignadas --}}
        @if($unitUserRecords->isNotEmpty())
            <div>
                <flux:label class="mb-3 block">Unidades asignadas</flux:label>
                <div class="space-y-3">
                    @foreach($unitUserRecords as $uu)
                        <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 px-4 py-3">
                            <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100 mb-2">
                                {{ $uu->unit->full_identifier }}
                            </p>
                            <div class="flex flex-wrap gap-6">
                                <flux:checkbox
                                    wire:model="unitUsers.{{ $uu->id }}.is_owner"
                                    label="Propietario"
                                />
                                <flux:checkbox
                                    wire:model="unitUsers.{{ $uu->id }}.is_responsible"
                                    label="Responsable de pago"
                                />
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- Asignar nueva unidad --}}
        @if($availableUnits->isNotEmpty())
            <div>
                <flux:label class="mb-3 block">
                    {{ $unitUserRecords->isNotEmpty() ? 'Agregar otra unidad' : 'Asignar unidad' }}
                </flux:label>
                <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 px-4 py-4 space-y-4">
                    <flux:field>
                        <flux:label>Unidad funcional</flux:label>
                        <flux:select wire:model="newUnitId" placeholder="Seleccionar unidad...">
                            <option value="">— Sin asignar —</option>
                            @foreach($availableUnits as $unit)
                                <option value="{{ $unit->id }}">
                                    {{ $unit->building->name }} — Depto {{ $unit->number }}
                                    @if($unit->owner) ({{ $unit->owner }}) @endif
                                </option>
                            @endforeach
                        </flux:select>
                        <flux:error name="newUnitId" />
                    </flux:field>

                    <div class="flex flex-wrap gap-6">
                        <flux:checkbox wire:model="newIsOwner" label="Propietario" />
                        <flux:checkbox wire:model="newIsResponsible" label="Responsable de pago" />
                    </div>
                </div>
            </div>
        @endif

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
