<div>
    <div class="mb-6">
        <flux:heading size="xl">Editar invitado</flux:heading>
    </div>

    <form wire:submit="update" class="space-y-6">
        <flux:field>
            <flux:label>Unidad <span class="text-red-500">*</span></flux:label>
            <flux:select wire:model="unitId" :disabled="$unitUsers->isEmpty()">
                @foreach($unitUsers as $unitUser)
                    <option value="{{ $unitUser->unit_id }}">
                        {{ $unitUser->unit->full_identifier }} ({{ $unitUser->unit->building->complex->name }})
                    </option>
                @endforeach
            </flux:select>
            <flux:error name="unitId" />
        </flux:field>

        <flux:field>
            <flux:label>Nombre <span class="text-red-500">*</span></flux:label>
            <flux:input wire:model="name" placeholder="Nombre y apellido" />
            <flux:error name="name" />
        </flux:field>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <flux:field>
                <flux:label>Fecha de nacimiento</flux:label>
                <flux:input type="date" wire:model="birthDate" />
                <flux:error name="birthDate" />
                <flux:description>Para identificar menores de edad.</flux:description>
            </flux:field>

            <flux:field>
                <flux:label>Foto (opcional)</flux:label>
                @if($guest->profilePhotoUrl())
                    <img src="{{ $guest->profilePhotoUrl() }}" alt="{{ $guest->name }}" class="h-16 w-16 rounded-full object-cover mb-2" />
                @endif
                <input type="file" accept="image/*" wire:model="photo" class="block w-full text-sm" />
                <flux:error name="photo" />
                <flux:description>Subir una nueva reemplaza la anterior.</flux:description>
            </flux:field>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:field>
                <flux:label>Tipo de documento</flux:label>
                <flux:select wire:model="documentType" placeholder="Seleccione tipo">
                    <option value="">-</option>
                    <option value="DNI">DNI</option>
                    <option value="Pasaporte">Pasaporte</option>
                    <option value="LC">LC</option>
                    <option value="LE">LE</option>
                    <option value="Otro">Otro</option>
                </flux:select>
                <flux:error name="documentType" />
            </flux:field>

            <flux:field>
                <flux:label>Número</flux:label>
                <flux:input wire:model="documentNumber" placeholder="Número" />
                <flux:error name="documentNumber" />
            </flux:field>
        </div>

        <flux:field>
            <flux:label>Teléfono</flux:label>
            <flux:input wire:model="phone" placeholder="Opcional" />
            <flux:error name="phone" />
        </flux:field>

        <flux:field>
            <flux:label>Notas</flux:label>
            <flux:textarea wire:model="notes" rows="3" placeholder="Opcional" />
            <flux:error name="notes" />
        </flux:field>

        <div class="flex gap-4">
            <flux:button type="submit" variant="primary">Actualizar</flux:button>
            <flux:button href="{{ route('resident.pools.guests.index') }}" variant="ghost" wire:navigate>Cancelar</flux:button>
        </div>
    </form>
</div>
