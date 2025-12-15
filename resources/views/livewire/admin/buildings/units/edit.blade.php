<div>
    <div class="mb-6">
        <flux:heading size="xl">Editar Unidad Funcional</flux:heading>
        <p class="text-sm text-gray-500 mt-1">Edificio: {{ $building->name }}</p>
    </div>

    <form wire:submit="update" class="space-y-6">
        <flux:field>
            <flux:label>Número de Unidad <span class="text-red-500">*</span></flux:label>
            <flux:input wire:model="number" placeholder="Ej: 1102" />
            <flux:error name="number" />
            <flux:description>Número único de la unidad funcional en este edificio</flux:description>
        </flux:field>

        <flux:field>
            <flux:label>Piso</flux:label>
            <flux:input wire:model="floor" placeholder="Ej: 11" />
            <flux:error name="floor" />
        </flux:field>

        <flux:field>
            <flux:label>Coeficiente <span class="text-red-500">*</span></flux:label>
            <flux:input type="number" wire:model="coefficient" step="0.0001" min="0" max="9999.9999" placeholder="1.0000" />
            <flux:error name="coefficient" />
            <flux:description>Coeficiente para el cálculo de expensas</flux:description>
        </flux:field>

        <div class="grid grid-cols-3 gap-4">
            <flux:field>
                <flux:label>Ambientes</flux:label>
                <flux:input type="number" wire:model="rooms" min="1" max="4" placeholder="Ej: 2" />
                <flux:error name="rooms" />
                <flux:description>Cantidad de ambientes (1, 2, 3 o 4)</flux:description>
            </flux:field>

            <flux:field>
                <flux:label>Terrazas</flux:label>
                <flux:input type="number" wire:model="terrazas" min="0" placeholder="Ej: 1" />
                <flux:error name="terrazas" />
                <flux:description>Cantidad de terrazas</flux:description>
            </flux:field>

            <flux:field>
                <flux:label>Área (m²)</flux:label>
                <flux:input type="number" wire:model="area" step="0.01" min="0" placeholder="Ej: 85.50" />
                <flux:error name="area" />
            </flux:field>
        </div>

        <flux:field>
            <flux:label>Notas</flux:label>
            <flux:textarea wire:model="notes" placeholder="Notas adicionales sobre la unidad funcional" rows="4" />
            <flux:error name="notes" />
        </flux:field>

        <div class="flex gap-4">
            <flux:button type="submit" variant="primary">
                Actualizar
            </flux:button>
            <flux:button href="{{ route('admin.buildings.units.index', $building) }}" variant="ghost">
                Cancelar
            </flux:button>
        </div>
    </form>
</div>
