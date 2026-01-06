<div>
    <div class="mb-6">
        <flux:heading size="xl">Invitados Utilizados</flux:heading>
        <p class="text-sm text-gray-500 mt-1">Historial de uso de tus invitados en las piletas.</p>
    </div>

    {{-- Selector de unidad --}}
    @if($unitUsers->count() > 1)
        <div class="mb-4">
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
    <div class="mb-4">
        <flux:field>
            <flux:label>Mes</flux:label>
            <flux:input type="month" wire:model.live="filterMonth" max="{{ now()->format('Y-m') }}" />
            <flux:description>Seleccioná el mes para ver el historial de invitados utilizados</flux:description>
        </flux:field>
    </div>

    {{-- Panel de límites e información --}}
    @if($limitsInfo)
        @php
            $hasWeekdayLimit = ($limitsInfo['available_weekdays_month'] ?? 0) <= 0;
            $hasWeekendLimit = ($limitsInfo['available_weekends_month'] ?? 0) <= 0;
            $alertColor = ($hasWeekdayLimit || $hasWeekendLimit) ? 'red' : (($limitsInfo['available_weekdays_month'] ?? 0) <= 1 || ($limitsInfo['available_weekends_month'] ?? 0) <= 1 ? 'yellow' : 'blue');
        @endphp
        <flux:callout color="{{ $alertColor }}" class="mb-4">
            <div class="space-y-3">
                <div class="font-bold text-base">🏊 Límites mensuales de invitados</div>
                
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    Los invitados pueden reingresar el mismo día cuantas veces quieran. Los límites son por tipo de día durante todo el mes.
                </div>

                <flux:separator />

                {{-- Uso del mes seleccionado --}}
                <div>
                    <div class="font-semibold text-sm mb-3">📊 Uso en {{ \Carbon\Carbon::parse($filterMonth . '-01')->locale('es')->isoFormat('MMMM YYYY') }}</div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Días de semana --}}
                        <div class="space-y-2 p-3 rounded-lg bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800">
                            <div class="font-semibold text-sm">📅 Días de semana</div>
                            <div class="text-sm">
                                Límite mensual: <span class="font-bold">{{ $limitsInfo['max_guests_weekday_month'] }}</span> invitados únicos
                            </div>
                            <div class="text-sm">
                                Usados: <span class="font-bold">{{ $limitsInfo['used_weekdays_month'] }}</span>
                            </div>
                            <div class="text-sm">
                                Disponible: <span class="font-bold {{ $limitsInfo['available_weekdays_month'] <= 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">{{ $limitsInfo['available_weekdays_month'] }}</span>
                            </div>
                        </div>
                        
                        {{-- Fines de semana --}}
                        <div class="space-y-2 p-3 rounded-lg bg-orange-50 dark:bg-orange-900/20 border border-orange-200 dark:border-orange-800">
                            <div class="font-semibold text-sm">🌞 Fines de semana</div>
                            <div class="text-sm">
                                Límite mensual: <span class="font-bold">{{ $limitsInfo['max_guests_weekend_month'] }}</span> invitados únicos
                            </div>
                            <div class="text-sm">
                                Usados: <span class="font-bold">{{ $limitsInfo['used_weekends_month'] }}</span>
                            </div>
                            <div class="text-sm">
                                Disponible: <span class="font-bold {{ $limitsInfo['available_weekends_month'] <= 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">{{ $limitsInfo['available_weekends_month'] }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Info de hoy si es el mes actual --}}
                @if($limitsInfo['today'])
                    <flux:separator />
                    <div>
                        <div class="font-semibold text-sm mb-2">⏰ Hoy ({{ $limitsInfo['today']['is_weekend'] ? 'Fin de semana' : 'Día de semana' }})</div>
                        <div class="space-y-1 pl-4">
                            <div class="text-sm">
                                Invitados únicos usados: <span class="font-bold">{{ $limitsInfo['today']['used_today'] }}</span> de {{ $limitsInfo['today']['max_today'] }}
                            </div>
                            <div class="text-sm">
                                Disponible hoy: <span class="font-bold {{ $limitsInfo['today']['available_today'] <= 0 ? 'text-red-600 dark:text-red-400' : 'text-green-600 dark:text-green-400' }}">{{ $limitsInfo['today']['available_today'] }}</span>
                            </div>
                        </div>
                    </div>
                @endif

                @if($hasWeekdayLimit && $hasWeekendLimit)
                    <div class="mt-2 pt-2 border-t border-red-300 dark:border-red-700">
                        <span class="text-red-600 dark:text-red-400 font-bold text-sm">⚠️ LÍMITES MENSUALES AGOTADOS - No se pueden agregar más invitados nuevos este mes (ni semana ni fin de semana)</span>
                    </div>
                @elseif($hasWeekdayLimit)
                    <div class="mt-2 pt-2 border-t border-red-300 dark:border-red-700">
                        <span class="text-red-600 dark:text-red-400 font-bold text-sm">⚠️ LÍMITE DE DÍAS DE SEMANA AGOTADO - No se pueden agregar más invitados nuevos en días de semana este mes</span>
                    </div>
                @elseif($hasWeekendLimit)
                    <div class="mt-2 pt-2 border-t border-red-300 dark:border-red-700">
                        <span class="text-red-600 dark:text-red-400 font-bold text-sm">⚠️ LÍMITE DE FINES DE SEMANA AGOTADO - No se pueden agregar más invitados nuevos en fines de semana este mes</span>
                    </div>
                @endif
            </div>
        </flux:callout>
    @endif

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
        <div class="mb-4">
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
    @endif
</div>
