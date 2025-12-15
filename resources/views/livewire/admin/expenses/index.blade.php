<div>
    <div class="mb-6 flex gap-4 items-center justify-between">
        <flux:heading size="xl">Gestión de Expensas</flux:heading>
        {{-- TODO: Implementar creación de expensas --}}
        {{-- <flux:button href="{{ route('admin.expenses.create') }}" variant="primary">
            Nueva Expensa
        </flux:button> --}}
    </div>

    <div class="mb-6 flex gap-4">
        <flux:field>
            <flux:label>Edificio</flux:label>
            <flux:select wire:model.live="buildingId" placeholder="Todos los edificios">
                <option value="">Todos los edificios</option>
                @foreach($buildings as $building)
                    <option value="{{ $building->id }}">{{ $building->complex->name }} - {{ $building->name }}</option>
                @endforeach
            </flux:select>
        </flux:field>

        <flux:field>
            <flux:label>Tipo</flux:label>
            <flux:select wire:model.live="type" placeholder="Todos los tipos">
                <option value="">Todos los tipos</option>
                <option value="{{ \App\ExpenseType::Ordinaria->value }}">Ordinaria</option>
                <option value="{{ \App\ExpenseType::Extraordinaria->value }}">Extraordinaria</option>
            </flux:select>
        </flux:field>

        <flux:field>
            <flux:label>Buscar</flux:label>
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por descripción..." />
        </flux:field>

        <div class="flex items-end">
            <flux:button wire:click="resetFilters" variant="ghost">Limpiar</flux:button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b">
                    <th class="text-left p-2">Edificio</th>
                    <th class="text-left p-2">Tipo</th>
                    <th class="text-left p-2">Concepto</th>
                    <th class="text-left p-2">Vencimiento</th>
                    <th class="text-right p-2">Monto Total</th>
                    <th class="text-right p-2">Pagado</th>
                    <th class="text-right p-2">Pendiente</th>
                    <th class="text-center p-2">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($expenses as $expense)
                    <tr class="border-b hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors">
                        <td class="p-2">{{ $expense->building->complex->name }} - {{ $expense->building->name }}</td>
                        <td class="p-2">
                            <flux:badge color="{{ $expense->type === \App\ExpenseType::Ordinaria ? 'blue' : 'purple' }}">
                                {{ $expense->type->label() }}
                            </flux:badge>
                        </td>
                        <td class="p-2">{{ $expense->concept?->name ?? '-' }}</td>
                        <td class="p-2">{{ $expense->due_date->format('d/m/Y') }}</td>
                        <td class="p-2 text-right">${{ number_format($expense->total_amount, 2) }}</td>
                        <td class="p-2 text-right text-green-600">${{ number_format($expense->total_paid, 2) }}</td>
                        <td class="p-2 text-right text-red-600">${{ number_format($expense->total_pending, 2) }}</td>
                        <td class="p-2 text-center">
                            {{-- TODO: Implementar vista de detalle de expensa --}}
                            {{-- <flux:button href="{{ route('admin.expenses.show', $expense) }}" variant="ghost" size="sm">
                                Ver
                            </flux:button> --}}
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="p-8 text-center text-gray-500">
                            No se encontraron expensas
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $expenses->links() }}
    </div>
</div>
