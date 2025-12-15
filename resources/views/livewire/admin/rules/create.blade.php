<div>
    <div class="mb-6">
        <flux:heading size="xl">Crear Regla del Sistema</flux:heading>
    </div>

    @if(session('error'))
        <flux:callout color="red" class="mb-4">
            {{ session('error') }}
        </flux:callout>
    @endif

    <form wire:submit="save" class="space-y-6">
        <flux:field>
            <flux:label>Tipo de Regla <span class="text-red-500">*</span></flux:label>
            <flux:select wire:model.live="type" placeholder="Seleccione el tipo de regla">
                @foreach($ruleTypes as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </flux:select>
            <flux:error name="type" />
        </flux:field>

        <flux:field>
            <flux:label>Nombre <span class="text-red-500">*</span></flux:label>
            <flux:input wire:model="name" placeholder="Nombre descriptivo de la regla" />
            <flux:error name="name" />
        </flux:field>

        <flux:field>
            <flux:label>Descripción</flux:label>
            <flux:textarea wire:model="description" placeholder="Descripción de la regla" rows="3" />
            <flux:error name="description" />
        </flux:field>

        {{-- Condiciones según el tipo de regla --}}
        @if($type === 'unit_occupancy')
            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Mínimo de Habitantes</flux:label>
                    <flux:input type="number" wire:model="conditions.min_occupants" placeholder="Ej: 1" min="0" />
                    <flux:description>Rango mínimo de habitantes para aplicar esta regla</flux:description>
                </flux:field>
                <flux:field>
                    <flux:label>Máximo de Habitantes</flux:label>
                    <flux:input type="number" wire:model="conditions.max_occupants" placeholder="Ej: 4" min="0" />
                    <flux:description>Rango máximo de habitantes para aplicar esta regla</flux:description>
                </flux:field>
            </div>
        @elseif($type === 'pool_weekly_guests')
            <flux:field>
                <flux:label>Días de la Semana</flux:label>
                <div class="flex flex-wrap gap-2">
                    @foreach(['Domingo' => 0, 'Lunes' => 1, 'Martes' => 2, 'Miércoles' => 3, 'Jueves' => 4, 'Viernes' => 5, 'Sábado' => 6] as $dayName => $dayValue)
                        <label class="flex items-center gap-2">
                            <input type="checkbox" wire:model="conditions.days_of_week" value="{{ $dayValue }}" class="rounded">
                            <span>{{ $dayName }}</span>
                        </label>
                    @endforeach
                </div>
                <flux:description>Seleccione los días de la semana en que aplica esta regla</flux:description>
            </flux:field>
        @endif

        {{-- Límites según el tipo de regla --}}
        @if($type === 'unit_occupancy')
            <flux:field>
                <flux:label>Máximo de Residentes Permitidos <span class="text-red-500">*</span></flux:label>
                <flux:input type="number" wire:model="limits.max_residents" placeholder="Ej: 4" min="1" required />
                <flux:error name="limits.max_residents" />
            </flux:field>
        @elseif($type === 'pool_weekly_guests')
            <flux:field>
                <flux:label>Máximo de Invitados <span class="text-red-500">*</span></flux:label>
                <flux:input type="number" wire:model="limits.max_guests" placeholder="Ej: 2" min="0" required />
                <flux:error name="limits.max_guests" />
            </flux:field>
        @elseif($type === 'pool_monthly_guests')
            <flux:field>
                <flux:label>Máximo de Invitados por Mes <span class="text-red-500">*</span></flux:label>
                <flux:input type="number" wire:model="limits.max_guests_per_month" placeholder="Ej: 10" min="0" required />
                <flux:error name="limits.max_guests_per_month" />
            </flux:field>
        @endif

        <flux:field>
            <flux:label>Mensaje Personalizado</flux:label>
            <flux:textarea wire:model="limits.message" placeholder="Mensaje a mostrar cuando se viola la regla" rows="2" />
            <flux:description>Opcional: mensaje personalizado para cuando se exceda el límite</flux:description>
        </flux:field>

        <div class="grid grid-cols-2 gap-4">
            <flux:field>
                <flux:label>Fecha de Inicio</flux:label>
                <flux:input type="date" wire:model="validFrom" />
                <flux:error name="validFrom" />
            </flux:field>

            <flux:field>
                <flux:label>Fecha de Fin</flux:label>
                <flux:input type="date" wire:model="validTo" />
                <flux:error name="validTo" />
                <flux:description>Dejar vacío para regla permanente</flux:description>
            </flux:field>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:field>
                <flux:label>Prioridad <span class="text-red-500">*</span></flux:label>
                <flux:input type="number" wire:model="priority" placeholder="0-100" min="0" max="100" required />
                <flux:error name="priority" />
                <flux:description>Mayor número = mayor prioridad (0-100)</flux:description>
            </flux:field>

            <flux:field>
                <flux:label>Estado</flux:label>
                <flux:checkbox wire:model="isActive">Regla activa</flux:checkbox>
                <flux:error name="isActive" />
            </flux:field>
        </div>

        <flux:field>
            <flux:label>Notas</flux:label>
            <flux:textarea wire:model="notes" placeholder="Notas adicionales sobre la regla" rows="3" />
            <flux:error name="notes" />
        </flux:field>

        <div class="flex gap-4">
            <flux:button type="submit" variant="primary">
                Guardar
            </flux:button>
            <flux:button href="{{ route('admin.rules.index') }}" variant="ghost">
                Cancelar
            </flux:button>
        </div>
    </form>
</div>
