<div class="container mx-auto px-4 py-6">
    <div class="bg-white rounded-lg shadow-md p-6">
        <h2 class="text-2xl font-bold mb-6">Configuración del SUM</h2>

        @if (session()->has('message'))
            <div class="mb-4 p-4 bg-green-100 text-green-700 rounded-lg">
                {{ session('message') }}
            </div>
        @endif

        <form wire:submit.prevent="save">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Precio por Hora -->
                <div>
                    <label for="pricePerHour" class="block text-sm font-medium text-gray-700 mb-2">
                        Precio por Hora ($)
                    </label>
                    <input type="number" id="pricePerHour" wire:model="pricePerHour"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           min="0" step="100">
                    @error('pricePerHour') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Horario de Apertura -->
                <div>
                    <label for="openTime" class="block text-sm font-medium text-gray-700 mb-2">
                        Horario de Apertura
                    </label>
                    <input type="time" id="openTime" wire:model="openTime"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @error('openTime') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Horario de Cierre -->
                <div>
                    <label for="closeTime" class="block text-sm font-medium text-gray-700 mb-2">
                        Horario de Cierre
                    </label>
                    <input type="time" id="closeTime" wire:model="closeTime"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                    @error('closeTime') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Días Máximos de Anticipación -->
                <div>
                    <label for="maxDaysAdvance" class="block text-sm font-medium text-gray-700 mb-2">
                        Máximo de Días de Anticipación
                    </label>
                    <input type="number" id="maxDaysAdvance" wire:model="maxDaysAdvance"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           min="1" max="90">
                    @error('maxDaysAdvance') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Horas Mínimas de Aviso -->
                <div>
                    <label for="minHoursNotice" class="block text-sm font-medium text-gray-700 mb-2">
                        Horas Mínimas de Aviso
                    </label>
                    <input type="number" id="minHoursNotice" wire:model="minHoursNotice"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                           min="0" max="168">
                    @error('minHoursNotice') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <!-- Requiere Aprobación -->
                <div class="flex items-center">
                    <input type="checkbox" id="requiresApproval" wire:model="requiresApproval"
                           class="w-5 h-5 text-blue-600 border-gray-300 rounded focus:ring-2 focus:ring-blue-500">
                    <label for="requiresApproval" class="ml-3 text-sm font-medium text-gray-700">
                        Requiere Aprobación del Administrador
                    </label>
                </div>
            </div>

            <!-- Botón Guardar -->
            <div class="mt-6">
                <button type="submit"
                        class="px-6 py-3 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transition-colors">
                    Guardar Configuración
                </button>
            </div>
        </form>
    </div>
</div>
