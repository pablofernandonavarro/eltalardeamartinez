<div>
    <div class="mb-6">
        <flux:heading size="xl">⚙️ Configuración de Límites de Pileta</flux:heading>
        <p class="text-sm text-gray-500 mt-1">
            Configura dinámicamente los límites de invitados según el tipo de día.
        </p>
    </div>

    @if(session('message'))
        <flux:callout color="green" icon="check-circle" class="mb-6">
            {{ session('message') }}
        </flux:callout>
    @endif

    @if(session('info'))
        <flux:callout color="blue" icon="information-circle" class="mb-6">
            {{ session('info') }}
        </flux:callout>
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Panel de Configuración --}}
        <div class="p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg">
            <flux:heading size="lg" class="mb-4">🎯 Límites Actuales</flux:heading>

            <form wire:submit="save" class="space-y-6">
                {{-- Días de Semana --}}
                <div class="p-4 bg-blue-50 dark:bg-blue-900/20 border-2 border-blue-500 dark:border-blue-500 rounded-lg">
                    <flux:field>
                        <flux:label class="flex items-center gap-2 text-base font-semibold text-blue-900 dark:text-blue-100">
                            <span class="text-2xl">💼</span>
                            <span>Lunes a Viernes</span>
                        </flux:label>
                        <flux:description class="text-blue-700 dark:text-blue-300">
                            Máximo de invitados permitidos en días laborales
                        </flux:description>
                        <div class="flex items-center gap-4 mt-2">
                            <flux:input 
                                type="number" 
                                wire:model="maxGuestsWeekday" 
                                min="0" 
                                max="20"
                                class="w-24 text-xl font-bold text-center"
                            />
                            <span class="text-sm text-blue-700 dark:text-blue-300 font-medium">invitados por día</span>
                        </div>
                        <flux:error name="maxGuestsWeekday" />
                    </flux:field>
                </div>

                {{-- Fines de Semana --}}
                <div class="p-4 bg-orange-50 dark:bg-orange-900/20 border-2 border-orange-500 dark:border-orange-500 rounded-lg">
                    <flux:field>
                        <flux:label class="flex items-center gap-2 text-base font-semibold text-orange-900 dark:text-orange-100">
                            <span class="text-2xl">🌞</span>
                            <span>Sábados, Domingos y Feriados</span>
                        </flux:label>
                        <flux:description class="text-orange-700 dark:text-orange-300">
                            Máximo de invitados permitidos en fines de semana
                        </flux:description>
                        <div class="flex items-center gap-4 mt-2">
                            <flux:input 
                                type="number" 
                                wire:model="maxGuestsWeekend" 
                                min="0" 
                                max="20"
                                class="w-24 text-xl font-bold text-center"
                            />
                            <span class="text-sm text-orange-700 dark:text-orange-300 font-medium">invitados por día</span>
                        </div>
                        <flux:error name="maxGuestsWeekend" />
                    </flux:field>
                </div>

                {{-- Límite Mensual --}}
                <div class="p-4 bg-purple-50 dark:bg-purple-900/20 border-2 border-purple-500 dark:border-purple-500 rounded-lg">
                    <flux:field>
                        <flux:label class="flex items-center gap-2 text-base font-semibold text-purple-900 dark:text-purple-100">
                            <span class="text-2xl">📅</span>
                            <span>Límite Mensual de Invitados</span>
                        </flux:label>
                        <flux:description class="text-purple-700 dark:text-purple-300">
                            Máximo total de invitados permitidos por unidad por mes (independiente del día)
                        </flux:description>
                        <div class="flex items-center gap-4 mt-2">
                            <flux:input 
                                type="number" 
                                wire:model="maxGuestsMonth" 
                                min="0" 
                                max="50"
                                class="w-24 text-xl font-bold text-center"
                            />
                            <span class="text-sm text-purple-700 dark:text-purple-300 font-medium">invitados por mes</span>
                        </div>
                        <flux:error name="maxGuestsMonth" />
                    </flux:field>
                </div>

                {{-- Máximo de Ingresos Diarios --}}
                <div class="p-4 bg-green-50 dark:bg-green-900/20 border-2 border-green-500 dark:border-green-500 rounded-lg">
                    <flux:field>
                        <flux:label class="flex items-center gap-2 text-base font-semibold text-green-900 dark:text-green-100">
                            <span class="text-2xl">🚪</span>
                            <span>Máximo de Personas Simultáneas por Unidad</span>
                        </flux:label>
                        <flux:description class="text-green-700 dark:text-green-300">
                            Cuántas personas de una unidad pueden estar dentro de la pileta al mismo tiempo (0 = sin límite)
                        </flux:description>
                        <div class="flex items-center gap-4 mt-2">
                            <flux:input 
                                type="number" 
                                wire:model="maxEntriesPerDay" 
                                min="0" 
                                max="20"
                                class="w-24 text-xl font-bold text-center"
                            />
                            <span class="text-sm text-green-700 dark:text-green-300 font-medium">personas simultáneas</span>
                        </div>
                        <flux:error name="maxEntriesPerDay" />
                    </flux:field>
                </div>

                {{-- Pagos Extra --}}
                <div class="p-4 bg-red-50 dark:bg-red-900/20 border-2 border-red-500 dark:border-red-500 rounded-lg">
                    <flux:field>
                        <div class="flex items-center justify-between">
                            <div>
                                <flux:label class="flex items-center gap-2 text-base font-semibold text-red-900 dark:text-red-100">
                                    <span class="text-2xl">💰</span>
                                    <span>Permitir Pagos por Invitados Extra</span>
                                </flux:label>
                                <flux:description class="text-red-700 dark:text-red-300">
                                    ⚠️ Si se habilita, permite que los residentes paguen para exceder el límite
                                </flux:description>
                            </div>
                            <flux:switch wire:model="allowExtraPayment" />
                        </div>
                    </flux:field>

                    @if($allowExtraPayment)
                        <div class="mt-3 p-3 bg-yellow-100 dark:bg-yellow-900/30 border border-yellow-400 rounded text-sm text-yellow-800 dark:text-yellow-300">
                            <strong>⚠️ ADVERTENCIA:</strong> Esta opción permite que se viole el reglamento mediante pago. Úsala con precaución.
                        </div>
                    @endif
                </div>

                <div class="flex gap-3">
                    <flux:button type="submit" variant="primary">
                        <flux:icon.check class="size-5" />
                        Guardar Configuración
                    </flux:button>

                    <flux:button type="button" wire:click="resetToDefault" variant="ghost">
                        <flux:icon.arrow-path class="size-5" />
                        Restaurar Valores Predeterminados
                    </flux:button>
                </div>
            </form>
        </div>

        {{-- Panel de Vista Previa --}}
        <div class="space-y-6">
            <div class="p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg">
                <flux:heading size="lg" class="mb-4">👁️ Vista Previa</flux:heading>

                <div class="space-y-4">
                    <div class="p-4 bg-gradient-to-br from-blue-50 to-cyan-50 dark:from-blue-950/20 dark:to-cyan-950/20 border-2 border-blue-400 rounded-lg">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="h-10 w-10 rounded-full bg-blue-500 flex items-center justify-center text-white text-lg">
                                💼
                            </div>
                            <div>
                                <div class="text-xs uppercase tracking-wider text-blue-600 dark:text-blue-400 font-semibold">
                                    DÍA LABORAL (LUNES A VIERNES)
                                </div>
                                <div class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                    Hoy: {{ now()->translatedFormat('l, d \d\e F') }}
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-gray-600 dark:text-gray-400">Cupo Según Reglamento:</span>
                            <span class="text-4xl font-black text-blue-600 dark:text-blue-400">
                                {{ $maxGuestsWeekday }}
                            </span>
                        </div>
                    </div>

                    <div class="p-4 bg-gradient-to-br from-orange-50 to-amber-50 dark:from-orange-950/20 dark:to-amber-950/20 border-2 border-orange-400 rounded-lg">
                        <div class="flex items-center gap-3 mb-2">
                            <div class="h-10 w-10 rounded-full bg-orange-500 flex items-center justify-center text-white text-lg">
                                🌞
                            </div>
                            <div>
                                <div class="text-xs uppercase tracking-wider text-orange-600 dark:text-orange-400 font-semibold">
                                    FIN DE SEMANA / FERIADO
                                </div>
                                <div class="text-sm font-medium text-gray-600 dark:text-gray-400">
                                    Sábados, Domingos y días no laborales
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-semibold text-gray-600 dark:text-gray-400">Cupo Según Reglamento:</span>
                            <span class="text-4xl font-black text-orange-600 dark:text-orange-400">
                                {{ $maxGuestsWeekend }}
                            </span>
                        </div>
                    </div>

                    @if($allowExtraPayment)
                        <div class="p-3 bg-red-50 dark:bg-red-950/30 border-2 border-red-500 rounded-lg">
                            <div class="flex items-center gap-2 text-red-700 dark:text-red-400 text-sm font-semibold">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                                <span>PAGOS POR INVITADOS EXTRA: HABILITADO</span>
                            </div>
                        </div>
                    @else
                        <div class="p-3 bg-green-50 dark:bg-green-950/30 border-2 border-green-500 rounded-lg">
                            <div class="flex items-center gap-2 text-green-700 dark:text-green-400 text-sm font-semibold">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                <span>✅ REGLAMENTO ESTRICTO: No se aceptan pagos extra</span>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Tabla de configuraciones técnicas --}}
            <div class="p-6 bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-700 rounded-lg">
                <flux:heading size="lg" class="mb-4">🔧 Configuraciones Técnicas</flux:heading>

                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-zinc-200 dark:border-zinc-700">
                                <th class="text-left py-2 px-3 font-semibold">Clave</th>
                                <th class="text-left py-2 px-3 font-semibold">Valor</th>
                                <th class="text-left py-2 px-3 font-semibold">Tipo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($allSettings as $setting)
                                <tr class="border-b border-zinc-100 dark:border-zinc-800">
                                    <td class="py-2 px-3 font-mono text-xs">{{ $setting->key }}</td>
                                    <td class="py-2 px-3">
                                        <span class="px-2 py-1 bg-zinc-100 dark:bg-zinc-800 rounded text-xs font-semibold">
                                            {{ $setting->value }}
                                        </span>
                                    </td>
                                    <td class="py-2 px-3">
                                        <span class="px-2 py-1 bg-blue-100 dark:bg-blue-900/30 text-blue-700 dark:text-blue-400 rounded text-xs">
                                            {{ $setting->type }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
