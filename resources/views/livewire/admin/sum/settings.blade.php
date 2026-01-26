<div class="p-4 lg:p-6">
    <div class="mx-auto max-w-7xl">
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <h2 class="mb-6 text-2xl font-bold text-zinc-900 dark:text-white">Configuración del SUM</h2>

            @if (session()->has('message'))
                <div class="mb-4 rounded-lg border border-green-200 bg-green-100 p-4 text-green-700 dark:border-green-800 dark:bg-green-900/20 dark:text-green-200">
                    {{ session('message') }}
                </div>
            @endif

            <form wire:submit.prevent="save">
                <div class="grid grid-cols-1 gap-6 md:grid-cols-2">
                    <!-- Precio por Hora -->
                    <div>
                        <label for="pricePerHour" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            Precio por Hora ($)
                        </label>
                        <input type="number" id="pricePerHour" wire:model="pricePerHour"
                               class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white focus:border-transparent focus:ring-2 focus:ring-blue-500"
                               min="0" step="100">
                        @error('pricePerHour') <span class="text-sm text-red-500 dark:text-red-400">{{ $message }}</span> @enderror
                    </div>

                    <!-- Horario de Apertura -->
                    <div>
                        <label for="openTime" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            Horario de Apertura
                        </label>
                        <input type="time" id="openTime" wire:model="openTime"
                               class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white focus:border-transparent focus:ring-2 focus:ring-blue-500">
                        @error('openTime') <span class="text-sm text-red-500 dark:text-red-400">{{ $message }}</span> @enderror
                    </div>

                    <!-- Horario de Cierre -->
                    <div>
                        <label for="closeTime" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            Horario de Cierre
                        </label>
                        <input type="time" id="closeTime" wire:model="closeTime"
                               class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white focus:border-transparent focus:ring-2 focus:ring-blue-500">
                        @error('closeTime') <span class="text-sm text-red-500 dark:text-red-400">{{ $message }}</span> @enderror
                    </div>

                    <!-- Días Máximos de Anticipación -->
                    <div>
                        <label for="maxDaysAdvance" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            Máximo de Días de Anticipación
                        </label>
                        <input type="number" id="maxDaysAdvance" wire:model="maxDaysAdvance"
                               class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white focus:border-transparent focus:ring-2 focus:ring-blue-500"
                               min="1" max="90">
                        @error('maxDaysAdvance') <span class="text-sm text-red-500 dark:text-red-400">{{ $message }}</span> @enderror
                    </div>

                    <!-- Horas Mínimas de Aviso -->
                    <div>
                        <label for="minHoursNotice" class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            Horas Mínimas de Aviso
                        </label>
                        <input type="number" id="minHoursNotice" wire:model="minHoursNotice"
                               class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white focus:border-transparent focus:ring-2 focus:ring-blue-500"
                               min="0" max="168">
                        @error('minHoursNotice') <span class="text-sm text-red-500 dark:text-red-400">{{ $message }}</span> @enderror
                    </div>

                    <!-- Requiere Aprobación -->
                    <div class="flex items-center">
                        <input type="checkbox" id="requiresApproval" wire:model="requiresApproval"
                               class="h-5 w-5 rounded border-zinc-300 text-blue-600 focus:ring-2 focus:ring-blue-500 dark:border-zinc-600">
                        <label for="requiresApproval" class="ml-3 text-sm font-medium text-zinc-700 dark:text-zinc-300">
                            Requiere Aprobación del Administrador
                        </label>
                    </div>
                </div>

                <!-- Botón Guardar -->
                <div class="mt-6">
                    <button type="submit"
                            class="rounded-lg bg-blue-600 px-6 py-3 font-medium text-white transition-colors hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 dark:focus:ring-offset-zinc-900">
                        Guardar Configuración
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
