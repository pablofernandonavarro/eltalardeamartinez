<div class="p-6">
    <div class="max-w-4xl mx-auto">
        <div class="mb-6">
            <h1 class="text-2xl font-semibold text-zinc-900 dark:text-white">Nueva Novedad</h1>
        </div>

        <form wire:submit="save" class="bg-white dark:bg-zinc-800 rounded-lg shadow p-6 space-y-6">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Título *</label>
                    <input type="text" wire:model="title" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white" required>
                    @error('title') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Descripción *</label>
                    <textarea wire:model="description" rows="4" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white" required></textarea>
                    @error('description') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Fecha del Evento *</label>
                    <input type="date" wire:model="event_date" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white" required>
                    @error('event_date') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Orden</label>
                    <input type="number" wire:model="order" min="0" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white">
                    @error('order') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Tipo de Ícono *</label>
                    <select wire:model="icon_type" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white" required>
                        <option value="clock">⏰ Reloj (Horarios)</option>
                        <option value="document">📄 Documento (Reunión)</option>
                        <option value="check">✅ Check (Completado)</option>
                    </select>
                    @error('icon_type') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-2">Esquema de Color *</label>
                    <select wire:model="color_scheme" class="w-full px-4 py-2 border border-zinc-300 dark:border-zinc-600 rounded-lg bg-white dark:bg-zinc-700 text-zinc-900 dark:text-white" required>
                        <option value="orange">🟠 Naranja/Ámbar</option>
                        <option value="blue">🔵 Azul</option>
                        <option value="green">🟢 Verde</option>
                    </select>
                    @error('color_scheme') <span class="text-red-500 text-sm">{{ $message }}</span> @enderror
                </div>

                <div class="md:col-span-2 flex gap-6">
                    <label class="flex items-center gap-2 text-zinc-700 dark:text-zinc-300">
                        <input type="checkbox" wire:model="is_featured" class="rounded">
                        <span>⭐ Marcar como destacada (badge "NUEVO")</span>
                    </label>

                    <label class="flex items-center gap-2 text-zinc-700 dark:text-zinc-300">
                        <input type="checkbox" wire:model="publish_now" class="rounded">
                        <span>📢 Publicar inmediatamente</span>
                    </label>
                </div>
            </div>

            <div class="flex justify-end gap-4 pt-4 border-t border-zinc-200 dark:border-zinc-700">
                <a href="{{ route('admin.news.index') }}" class="px-4 py-2 border border-zinc-300 dark:border-zinc-600 text-zinc-700 dark:text-zinc-300 rounded-lg hover:bg-zinc-50 dark:hover:bg-zinc-700">
                    Cancelar
                </a>
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg">
                    Guardar Novedad
                </button>
            </div>
        </form>
    </div>
</div>
