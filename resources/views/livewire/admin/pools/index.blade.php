<div>
    <div class="mb-6 flex gap-4 items-center justify-between">
        <flux:heading size="xl">Registro de Ingresos a Piletas</flux:heading>
        <flux:button href="{{ route('admin.pools.register-entry') }}" variant="primary">
            Registrar Ingreso
        </flux:button>
    </div>

    <div class="mb-6 flex gap-4">
        <flux:field>
            <flux:label>Pileta</flux:label>
            <flux:select wire:model.live="poolId" placeholder="Todas las piletas">
                <option value="">Todas las piletas</option>
                @foreach($pools as $pool)
                    <option value="{{ $pool->id }}">{{ $pool->name }}</option>
                @endforeach
            </flux:select>
        </flux:field>

        <flux:field>
            <flux:label>Fecha</flux:label>
            <flux:input type="date" wire:model.live="date" />
        </flux:field>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b">
                    <th class="text-left p-2">Fecha/Hora</th>
                    <th class="text-left p-2">Pileta</th>
                    <th class="text-left p-2">Unidad</th>
                    <th class="text-left p-2">Usuario</th>
                    <th class="text-center p-2">Invitados</th>
                </tr>
            </thead>
            <tbody>
                @forelse($entries as $entry)
                    <tr class="border-b hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors">
                        <td class="p-2">{{ $entry->entered_at->format('d/m/Y H:i') }}</td>
                        <td class="p-2">{{ $entry->pool->name }}</td>
                        <td class="p-2">{{ $entry->unit->full_identifier }}</td>
                        <td class="p-2">{{ $entry->user->name }}</td>
                        <td class="p-2 text-center">{{ $entry->guests_count }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-gray-500">
                            No se encontraron registros de ingreso
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $entries->links() }}
    </div>
</div>
