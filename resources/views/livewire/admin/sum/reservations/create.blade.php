<div class="max-w-2xl mx-auto space-y-6">

    <div class="flex items-center justify-between">
        <div>
            <flux:heading size="xl">Nueva reserva SUM</flux:heading>
            <flux:subheading>Creá una reserva manual para cualquier usuario.</flux:subheading>
        </div>
        <flux:button :href="route('admin.sum.reservations.index')" wire:navigate variant="ghost" icon="arrow-left">
            Volver
        </flux:button>
    </div>

    @if (session('message'))
        <flux:callout color="green" icon="check-circle">{{ session('message') }}</flux:callout>
    @endif

    <div class="rounded-xl border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
        <div class="space-y-5">

            {{-- Unidad --}}
            <flux:field>
                <flux:label>Unidad Funcional *</flux:label>
                <flux:select wire:model.live="unitId">
                    <option value="">— Seleccioná una unidad —</option>
                    @foreach ($units as $unit)
                        <option value="{{ $unit->id }}">{{ $unit->full_identifier }}</option>
                    @endforeach
                </flux:select>
                <flux:error name="unitId" />
            </flux:field>

            {{-- Usuario --}}
            <flux:field>
                <flux:label>Usuario *</flux:label>
                <flux:select wire:model="userId" :disabled="!$unitId">
                    <option value="">— Seleccioná un usuario —</option>
                    @foreach ($usersForUnit as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->email }})</option>
                    @endforeach
                </flux:select>
                @if ($unitId && $usersForUnit->isEmpty())
                    <flux:description class="text-amber-600">Esta unidad no tiene usuarios asignados.</flux:description>
                @endif
                <flux:error name="userId" />
            </flux:field>

            <div class="grid grid-cols-3 gap-4">
                {{-- Fecha --}}
                <flux:field>
                    <flux:label>Fecha *</flux:label>
                    <flux:input type="date" wire:model="date" />
                    <flux:error name="date" />
                </flux:field>

                {{-- Hora inicio --}}
                <flux:field>
                    <flux:label>Hora inicio *</flux:label>
                    <flux:select wire:model.live="startTime">
                        @foreach (range(0, 23) as $h)
                            <option value="{{ sprintf('%02d:00', $h) }}">{{ sprintf('%02d:00', $h) }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="startTime" />
                </flux:field>

                {{-- Hora fin --}}
                <flux:field>
                    <flux:label>Hora fin *</flux:label>
                    <flux:select wire:model="endTime" :disabled="!$startTime">
                        <option value="">—</option>
                        @foreach ($availableEndTimeSlots as $slot)
                            <option value="{{ $slot }}">{{ $slot }}</option>
                        @endforeach
                    </flux:select>
                    <flux:error name="endTime" />
                </flux:field>
            </div>

            {{-- Resumen de monto --}}
            @if ($calculatedHours > 0)
                <flux:callout color="blue" icon="calculator">
                    {{ number_format($calculatedHours, 1) }} hs × ${{ number_format($pricePerHour, 0, ',', '.') }}/hs
                    = <strong>${{ number_format($calculatedAmount, 2, ',', '.') }}</strong>
                </flux:callout>
            @endif

            {{-- Notas --}}
            <flux:field>
                <flux:label>Notas (opcional)</flux:label>
                <flux:textarea wire:model="notes" rows="2" placeholder="Observaciones internas..." />
            </flux:field>

            {{-- Marcar como pagado --}}
            <flux:field>
                <div class="flex items-center gap-3">
                    <flux:checkbox wire:model="markAsPaid" id="mark-paid" />
                    <flux:label for="mark-paid">Marcar como pagado (efectivo)</flux:label>
                </div>
                <flux:description>Si no marcás esta opción, el pago quedará pendiente.</flux:description>
            </flux:field>

        </div>
    </div>

    <div class="flex justify-end gap-3">
        <flux:button :href="route('admin.sum.reservations.index')" wire:navigate variant="ghost">Cancelar</flux:button>
        <flux:button wire:click="save" variant="primary" wire:loading.attr="disabled" icon="check">
            <span wire:loading.remove wire:target="save">Crear reserva</span>
            <span wire:loading wire:target="save">Guardando...</span>
        </flux:button>
    </div>

</div>
