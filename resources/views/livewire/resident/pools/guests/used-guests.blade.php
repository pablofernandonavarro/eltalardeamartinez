<flux:main>
    <div class="space-y-6">
        <flux:heading size="xl">Invitados Utilizados</flux:heading>

        {{-- Selector de unidad --}}
        @if($unitUsers->count() > 1)
            <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 p-4">
                <flux:field>
                    <flux:label>Unidad</flux:label>
                    <flux:select wire:model.live="unitId">
                        @foreach($unitUsers as $uu)
                            <option value="{{ $uu->unit_id }}">
                                {{ $uu->unit->full_identifier }} - {{ $uu->unit->building->complex->name }}
                            </option>
                        @endforeach
                    </flux:select>
                </flux:field>
            </div>
        @elseif($unitUsers->count() === 1)
            <flux:callout color="blue" class="mb-4">
                <div class="font-semibold">{{ $unitUsers->first()->unit->full_identifier }}</div>
                <div class="text-sm">{{ $unitUsers->first()->unit->building->complex->name }}</div>
            </flux:callout>
        @endif

        {{-- Filtro por mes --}}
        <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800 p-4">
            <flux:field>
                <flux:label>Mes</flux:label>
                <flux:input type="month" wire:model.live="filterMonth" max="{{ now()->format('Y-m') }}" />
                <flux:description>Seleccioná el mes para ver el historial de invitados utilizados</flux:description>
            </flux:field>
        </div>

        {{-- Lista de invitados utilizados --}}
        @if($usedGuests->isEmpty())
            <flux:callout color="zinc">
                <div class="text-center py-8">
                    <div class="text-lg font-semibold mb-2">No hay invitados utilizados</div>
                    <div class="text-sm text-gray-500">
                        No hay registros de invitados utilizados en el mes seleccionado.
                    </div>
                </div>
            </flux:callout>
        @else
            <div class="space-y-4">
                <div class="flex items-center justify-between">
                    <div class="text-sm text-gray-500">
                        Total de ingresos: <span class="font-bold">{{ $usedGuests->count() }}</span>
                    </div>
                </div>

                <div class="space-y-3">
                    @foreach($usedGuests as $usage)
                        <div class="rounded-lg border border-zinc-200 dark:border-zinc-700 p-4 hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                            <div class="flex items-start gap-4">
                                {{-- Foto del invitado --}}
                                @if($usage->profile_photo_path)
                                    <img src="{{ asset('storage/' . $usage->profile_photo_path) }}" alt="{{ $usage->guest_name }}" class="h-16 w-16 rounded-full object-cover flex-shrink-0" />
                                @else
                                    <div class="h-16 w-16 rounded-full bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center text-sm font-semibold flex-shrink-0">
                                        {{ \Illuminate\Support\Str::of($usage->guest_name)->explode(' ')->take(2)->map(fn ($w) => \Illuminate\Support\Str::substr($w, 0, 1))->implode('') }}
                                    </div>
                                @endif

                                {{-- Información del uso --}}
                                <div class="flex-1 min-w-0">
                                    <div class="font-semibold text-lg">{{ $usage->guest_name }}</div>
                                    <div class="text-sm text-gray-500">
                                        {{ $usage->document_type }} {{ $usage->document_number }}
                                    </div>
                                    
                                    <div class="mt-2 space-y-1">
                                        <div class="flex items-center gap-2 text-sm">
                                            <flux:icon icon="calendar" class="size-4 text-gray-400" />
                                            <span class="font-medium">{{ \Carbon\Carbon::parse($usage->entered_at)->format('d/m/Y H:i') }}</span>
                                            @if($usage->exited_at)
                                                <span class="text-gray-500">→</span>
                                                <span>{{ \Carbon\Carbon::parse($usage->exited_at)->format('H:i') }}</span>
                                            @else
                                                <flux:badge color="green" size="sm">En pileta</flux:badge>
                                            @endif
                                        </div>

                                        <div class="flex items-center gap-2 text-sm text-gray-500">
                                            <flux:icon icon="map-pin" class="size-4" />
                                            <span>{{ $usage->pool_name }}</span>
                                        </div>

                                        @if($usage->entered_by_name)
                                            <div class="flex items-center gap-2 text-sm text-gray-500">
                                                <flux:icon icon="user" class="size-4" />
                                                <span>Ingresó con: {{ $usage->entered_by_name }}</span>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
</flux:main>
