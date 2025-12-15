<div>
    <div class="mb-6">
        <flux:heading size="xl">Registrar Ingreso a Pileta</flux:heading>
    </div>

    @if($errors->has('error'))
        <flux:callout color="red" class="mb-4">
            {{ $errors->first('error') }}
        </flux:callout>
    @endif

    <form wire:submit="registerEntry" class="space-y-6">
        <flux:field>
            <flux:label>Pileta</flux:label>
            <flux:select wire:model="poolId" placeholder="Seleccione una pileta">
                <option value="">Seleccione una pileta</option>
                @foreach($pools as $pool)
                    <option value="{{ $pool->id }}">{{ $pool->name }}</option>
                @endforeach
            </flux:select>
            <flux:error name="poolId" />
        </flux:field>

        <flux:field>
            <flux:label>Unidad</flux:label>
            <flux:select wire:model="unitId" placeholder="Seleccione una unidad">
                <option value="">Seleccione una unidad</option>
                @foreach($units as $unit)
                    <option value="{{ $unit->id }}">{{ $unit->full_identifier }}</option>
                @endforeach
            </flux:select>
            <flux:error name="unitId" />
        </flux:field>

        <flux:field>
            <flux:label>Usuario</flux:label>
            <flux:select wire:model="userId" placeholder="Seleccione un usuario">
                <option value="">Seleccione un usuario</option>
                @foreach($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->role?->label() ?? 'Sin rol' }})</option>
                @endforeach
            </flux:select>
            <flux:error name="userId" />
        </flux:field>

        <flux:field>
            <flux:label>Cantidad de Invitados</flux:label>
            <flux:input type="number" wire:model="guestsCount" min="0" max="10" />
            <flux:error name="guestsCount" />
        </flux:field>

        <flux:field>
            <flux:label>Fecha y Hora de Ingreso</flux:label>
            <flux:input type="datetime-local" wire:model="enteredAt" />
            <flux:error name="enteredAt" />
        </flux:field>

        <div class="flex gap-4">
            <flux:button type="submit" variant="primary">
                Registrar Ingreso
            </flux:button>
            <flux:button href="{{ route('admin.pools.index') }}" variant="ghost">
                Cancelar
            </flux:button>
        </div>
    </form>
</div>
