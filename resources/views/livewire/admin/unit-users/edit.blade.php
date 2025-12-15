<div>
    <div class="mb-6">
        <flux:heading size="xl">Editar Relación Usuario-Unidad</flux:heading>
    </div>

    @if(session('error'))
        <flux:callout color="red" class="mb-4">
            {{ session('error') }}
        </flux:callout>
    @endif

    <form wire:submit="save" class="space-y-6">
        <flux:field>
            <flux:label>Usuario <span class="text-red-500">*</span></flux:label>
            <flux:select wire:model="userId" placeholder="Seleccione un usuario">
                <option value="">Seleccione un usuario</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }}) - {{ $user->role?->label() ?? 'Sin rol' }}</option>
                @endforeach
            </flux:select>
            <flux:error name="userId" />
        </flux:field>

        <flux:field>
            <flux:label>Unidad Funcional <span class="text-red-500">*</span></flux:label>
            <flux:select wire:model="unitId" placeholder="Seleccione una unidad funcional">
                <option value="">Seleccione una unidad funcional</option>
                @foreach($units as $unit)
                    @if($unit->building && $unit->building->complex)
                        <option value="{{ $unit->id }}">{{ $unit->full_identifier }} - {{ $unit->building->complex->name }}</option>
                    @endif
                @endforeach
            </flux:select>
            <flux:error name="unitId" />
        </flux:field>

        <div class="grid grid-cols-2 gap-4">
            <flux:field>
                <flux:label>Es Propietario</flux:label>
                <flux:checkbox wire:model="isOwner" />
                <flux:error name="isOwner" />
                <flux:description>Solo usuarios con rol Propietario pueden ser propietarios</flux:description>
            </flux:field>

            <flux:field>
                <flux:label>Es Responsable del Pago</flux:label>
                <flux:checkbox wire:model="isResponsible" />
                <flux:error name="isResponsible" />
                <flux:description>Puede ser Propietario o Inquilino</flux:description>
            </flux:field>
        </div>

        <flux:field>
            <flux:label>Fecha de Inicio <span class="text-red-500">*</span></flux:label>
            <flux:input type="date" wire:model="startedAt" />
            <flux:error name="startedAt" />
        </flux:field>

        <flux:field>
            <flux:label>Fecha de Fin</flux:label>
            <flux:input type="date" wire:model="endedAt" />
            <flux:error name="endedAt" />
            <flux:description>Complete para finalizar la relación</flux:description>
        </flux:field>

        <flux:field>
            <flux:label>Notas</flux:label>
            <flux:textarea wire:model="notes" placeholder="Notas adicionales sobre esta asignación" rows="4" />
            <flux:error name="notes" />
        </flux:field>

        <div class="flex gap-4">
            <flux:button type="submit" variant="primary">
                Actualizar
            </flux:button>
            <flux:button href="{{ route('admin.unit-users.index') }}" variant="ghost">
                Cancelar
            </flux:button>
        </div>
    </form>
</div>
