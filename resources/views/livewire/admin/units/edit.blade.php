<div>
    <div class="mb-6">
        <flux:heading size="xl">Editar Unidad Funcional</flux:heading>
    </div>

    @if(session('message'))
        <flux:callout color="green" class="mb-4">
            {{ session('message') }}
        </flux:callout>
    @endif

    @if(session('error'))
        <flux:callout color="red" class="mb-4">
            {{ session('error') }}
        </flux:callout>
    @endif

    <form wire:submit="update" class="space-y-6">
        <flux:field>
            <flux:label>Edificio <span class="text-red-500">*</span></flux:label>
            <flux:select wire:model="buildingId" placeholder="Seleccione un edificio">
                <option value="">Seleccione un edificio</option>
                @foreach($buildings as $building)
                    <option value="{{ $building->id }}">
                        {{ $building->name }}
                        @if($building->complex)
                            - {{ $building->complex->name }}
                        @endif
                    </option>
                @endforeach
            </flux:select>
            <flux:error name="buildingId" />
        </flux:field>

        <flux:field>
            <flux:label>Número de Unidad <span class="text-red-500">*</span></flux:label>
            <flux:input wire:model="number" placeholder="Ej: 101, A1, etc." />
            <flux:error name="number" />
        </flux:field>

        <flux:field>
            <flux:label>Piso</flux:label>
            <flux:input wire:model="floor" placeholder="Ej: 1, PB, etc." />
            <flux:error name="floor" />
        </flux:field>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <flux:field>
                <flux:label>Coeficiente <span class="text-red-500">*</span></flux:label>
                <flux:input type="number" wire:model="coefficient" step="0.0001" min="0" max="9999.9999" placeholder="1.0000" />
                <flux:error name="coefficient" />
                <flux:description>Coeficiente de participación en expensas</flux:description>
            </flux:field>

            <flux:field>
                <flux:label>Área (m²)</flux:label>
                <flux:input type="number" wire:model="area" step="0.01" min="0" placeholder="Ej: 45.50" />
                <flux:error name="area" />
                <flux:description class="invisible">&nbsp;</flux:description>
            </flux:field>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
            <flux:field>
                <flux:label>Ambientes</flux:label>
                <flux:input type="number" wire:model="rooms" min="1" max="4" placeholder="1-4" />
                <flux:error name="rooms" />
                <flux:description>Cantidad de ambientes (1-4)</flux:description>
            </flux:field>

            <flux:field>
                <flux:label>Terrazas</flux:label>
                <flux:input type="number" wire:model="terrazas" min="0" placeholder="0" />
                <flux:error name="terrazas" />
                <flux:description class="invisible">&nbsp;</flux:description>
            </flux:field>
        </div>

        <flux:field>
            <flux:label>Notas</flux:label>
            <flux:textarea wire:model="notes" placeholder="Notas adicionales sobre esta unidad funcional" rows="4" />
            <flux:error name="notes" />
        </flux:field>

        <div class="flex gap-4">
            <flux:button type="submit" variant="primary">
                Actualizar
            </flux:button>
            <flux:button href="{{ route('admin.units.index') }}" variant="ghost">
                Cancelar
            </flux:button>
        </div>
    </form>
</div>
