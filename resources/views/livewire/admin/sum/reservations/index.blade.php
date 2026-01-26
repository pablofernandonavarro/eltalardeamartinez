<div class="p-4 lg:p-6">
    <div class="mx-auto max-w-7xl">
        <div class="rounded-lg border border-zinc-200 bg-white p-6 dark:border-zinc-700 dark:bg-zinc-900">
            <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                <h2 class="text-2xl font-bold text-zinc-900 dark:text-white">Reservas del SUM</h2>
                <button class="rounded-lg bg-blue-600 px-4 py-2 text-sm font-medium text-white transition-colors hover:bg-blue-700">
                    Nueva Reserva
                </button>
            </div>

            <!-- Filtros -->
            <div class="mb-6 grid grid-cols-1 gap-4 md:grid-cols-4">
                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Estado</label>
                    <select class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white focus:ring-2 focus:ring-blue-500">
                        <option value="">Todos</option>
                        <option value="pending">Pendiente</option>
                        <option value="approved">Aprobada</option>
                        <option value="rejected">Rechazada</option>
                        <option value="cancelled">Cancelada</option>
                    </select>
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Fecha Desde</label>
                    <input type="date" class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Fecha Hasta</label>
                    <input type="date" class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white focus:ring-2 focus:ring-blue-500">
                </div>

                <div>
                    <label class="mb-2 block text-sm font-medium text-zinc-700 dark:text-zinc-300">Buscar</label>
                    <input type="text" placeholder="Nombre o Unidad" class="w-full rounded-lg border border-zinc-300 bg-white px-4 py-2 text-sm dark:border-zinc-600 dark:bg-zinc-800 dark:text-white focus:ring-2 focus:ring-blue-500">
                </div>
            </div>

            <!-- Tabla de Reservas -->
            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-zinc-200 dark:divide-zinc-700">
                    <thead class="bg-zinc-50 dark:bg-zinc-800">
                        <tr>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">ID</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Residente</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Unidad</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Fecha</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Horario</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Horas</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Total</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Estado</th>
                            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-zinc-200 bg-white dark:divide-zinc-700 dark:bg-zinc-900">
                        <!-- Ejemplo de fila (esto se reemplazará con datos reales de Livewire) -->
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800/50">
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-900 dark:text-white">#001</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-900 dark:text-white">Juan Pérez</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-900 dark:text-white">A-101</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-900 dark:text-white">25/01/2026</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-900 dark:text-white">14:00 - 18:00</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm text-zinc-900 dark:text-white">4 hrs</td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-semibold text-zinc-900 dark:text-white">$2,000</td>
                            <td class="whitespace-nowrap px-6 py-4">
                                <span class="inline-flex rounded-full bg-yellow-100 px-2 py-1 text-xs font-semibold leading-5 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-200">
                                    Pendiente
                                </span>
                            </td>
                            <td class="whitespace-nowrap px-6 py-4 text-sm font-medium">
                                <button class="mr-3 text-blue-600 hover:text-blue-900 dark:text-blue-400 dark:hover:text-blue-300">Ver</button>
                                <button class="mr-3 text-green-600 hover:text-green-900 dark:text-green-400 dark:hover:text-green-300">Aprobar</button>
                                <button class="text-red-600 hover:text-red-900 dark:text-red-400 dark:hover:text-red-300">Rechazar</button>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="9" class="px-6 py-4 text-center text-zinc-500 dark:text-zinc-400">
                                No hay reservas registradas
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Paginación -->
            <div class="mt-6 flex items-center justify-between">
                <div class="text-sm text-zinc-700 dark:text-zinc-300">
                    Mostrando <span class="font-medium">1</span> a <span class="font-medium">10</span> de <span class="font-medium">0</span> resultados
                </div>
                <div class="flex gap-2">
                    <button class="rounded-lg border border-zinc-300 px-3 py-1 text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:text-zinc-300 dark:hover:bg-zinc-800" disabled>
                        Anterior
                    </button>
                    <button class="rounded-lg border border-zinc-300 px-3 py-1 text-zinc-700 transition-colors hover:bg-zinc-50 dark:border-zinc-600 dark:text-zinc-300 dark:hover:bg-zinc-800" disabled>
                        Siguiente
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
