<div>
    <div class="mb-6">
        <flux:heading size="xl">Editar Residente</flux:heading>
    </div>

    @if(session('error'))
        <flux:callout color="red" class="mb-4">
            {{ session('error') }}
        </flux:callout>
    @endif

    <form wire:submit="save" class="space-y-6">
        <flux:field>
            <flux:label>Unidad Funcional <span class="text-red-500">*</span></flux:label>
            <flux:select wire:model.live="unitId" placeholder="Seleccione una unidad funcional">
                <option value="">Seleccione una unidad funcional</option>
                @foreach($units as $unit)
                    @if($unit->building && $unit->building->complex)
                        <option value="{{ $unit->id }}">{{ $unit->full_identifier }} - {{ $unit->building->complex->name }}</option>
                    @endif
                @endforeach
            </flux:select>
            <flux:error name="unitId" />
        </flux:field>

        <flux:field>
            <flux:label>Nombre <span class="text-red-500">*</span></flux:label>
            <flux:input wire:model="name" placeholder="Nombre completo del residente" />
            <flux:error name="name" />
        </flux:field>

        <div class="grid grid-cols-2 gap-4">
            <flux:field>
                <flux:label>Tipo de Documento</flux:label>
                <flux:select wire:model="documentType" placeholder="Seleccione tipo">
                    <option value="">Seleccione tipo</option>
                    <option value="DNI">DNI</option>
                    <option value="Pasaporte">Pasaporte</option>
                    <option value="LC">LC</option>
                    <option value="LE">LE</option>
                    <option value="Otro">Otro</option>
                </flux:select>
                <flux:error name="documentType" />
            </flux:field>

            <flux:field>
                <flux:label>Número de Documento</flux:label>
                <flux:input wire:model="documentNumber" placeholder="Número de documento" />
                <flux:error name="documentNumber" />
            </flux:field>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:field>
                <flux:label>Fecha de Nacimiento</flux:label>
                <flux:input type="date" wire:model="birthDate" />
                <flux:error name="birthDate" />
                <flux:description>Para identificar menores de edad</flux:description>
            </flux:field>

            <flux:field>
                <flux:label>Relación</flux:label>
                <flux:select wire:model="relationship" placeholder="Seleccione relación">
                    <option value="">Seleccione relación</option>
                    <option value="Hijo/a">Hijo/a</option>
                    <option value="Cónyuge">Cónyuge</option>
                    <option value="Familiar">Familiar</option>
                    <option value="Otro">Otro</option>
                </flux:select>
                <flux:error name="relationship" />
            </flux:field>
        </div>

        <flux:field>
            <flux:label>Usuario Responsable</flux:label>
            <flux:select wire:model="userId" placeholder="Seleccione un usuario responsable (opcional)" :disabled="!$unitId">
                <option value="">Sin responsable</option>
                @if($unitId > 0)
                    @forelse($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }}) - {{ $user->role?->label() ?? 'Sin rol' }}</option>
                    @empty
                        <option value="" disabled>No hay usuarios activos en esta unidad</option>
                    @endforelse
                @else
                    <option value="" disabled>Primero seleccione una unidad funcional</option>
                @endif
            </flux:select>
            <flux:error name="userId" />
            <flux:description>Padre, tutor o responsable del residente. Solo se muestran usuarios con relación activa en la unidad seleccionada.</flux:description>
        </flux:field>

        <div class="grid grid-cols-2 gap-4">
            <flux:field>
                <flux:label>Fecha de Inicio</flux:label>
                <flux:input type="date" wire:model="startedAt" />
                <flux:error name="startedAt" />
            </flux:field>

            <flux:field>
                <flux:label>Fecha de Fin</flux:label>
                <flux:input type="date" wire:model="endedAt" />
                <flux:error name="endedAt" />
                <flux:description>Complete para finalizar la residencia</flux:description>
            </flux:field>
        </div>

        <flux:field>
            <flux:label>Notas</flux:label>
            <flux:textarea wire:model="notes" placeholder="Notas adicionales sobre el residente" rows="4" />
            <flux:error name="notes" />
        </flux:field>

        <div class="flex gap-4">
            <flux:button type="submit" variant="primary">
                Actualizar
            </flux:button>
            <flux:button href="{{ route('admin.residents.index') }}" variant="ghost">
                Cancelar
            </flux:button>
        </div>
    </form>
</div>
