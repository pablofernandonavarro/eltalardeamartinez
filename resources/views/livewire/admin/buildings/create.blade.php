<div>
    <div class="mb-6">
        <flux:heading size="xl">Crear Edificio</flux:heading>
    </div>

    <form wire:submit="save" class="space-y-6">
        <flux:field>
            <flux:label>Complejo <span class="text-red-500">*</span></flux:label>
            <flux:select wire:model="complexId" placeholder="Seleccione un complejo">
                <option value="">Seleccione un complejo</option>
                @foreach($complexes as $complex)
                    <option value="{{ $complex->id }}">{{ $complex->name }}</option>
                @endforeach
            </flux:select>
            <flux:error name="complexId" />
        </flux:field>

        <flux:field>
            <flux:label>Nombre del Edificio <span class="text-red-500">*</span></flux:label>
            <flux:input wire:model="name" placeholder="Ej: Torre A" />
            <flux:error name="name" />
        </flux:field>

        <flux:field>
            <flux:label>Dirección</flux:label>
            <flux:input wire:model="address" placeholder="Dirección del edificio" />
            <flux:error name="address" />
        </flux:field>

        <flux:field>
            <flux:label>Número de Pisos</flux:label>
            <flux:input type="number" wire:model="floors" min="1" placeholder="Ej: 10" />
            <flux:error name="floors" />
        </flux:field>

        <flux:field>
            <flux:label>Notas</flux:label>
            <flux:textarea wire:model="notes" placeholder="Notas adicionales sobre el edificio" rows="4" />
            <flux:error name="notes" />
        </flux:field>

        <div class="flex gap-4">
            <flux:button type="submit" variant="primary">
                Guardar
            </flux:button>
            <flux:button href="{{ route('admin.buildings.index') }}" variant="ghost">
                Cancelar
            </flux:button>
        </div>
    </form>
</div>
