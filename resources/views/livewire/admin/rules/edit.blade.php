<div>
    <div class="mb-6">
        <flux:heading size="xl">Editar Regla del Sistema</flux:heading>
    </div>

    @if(session('error'))
        <flux:callout color="red" class="mb-4">
            {{ session('error') }}
        </flux:callout>
    @endif

    <form wire:submit="save" class="space-y-6">
        <flux:field>
            <flux:label>Tipo de Regla <span class="text-red-500">*</span></flux:label>
            <flux:select wire:model.live="type" placeholder="Seleccione el tipo de regla">
                @foreach($ruleTypes as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                @endforeach
            </flux:select>
            <flux:error name="type" />
        </flux:field>

        <flux:field>
            <flux:label>Nombre <span class="text-red-500">*</span></flux:label>
            <flux:input wire:model="name" placeholder="Nombre descriptivo de la regla" />
            <flux:error name="name" />
        </flux:field>

        <flux:field>
            <flux:label>Descripción</flux:label>
            <flux:textarea wire:model="description" placeholder="Descripción de la regla" rows="3" />
            <flux:error name="description" />
        </flux:field>

        {{-- Condiciones según el tipo de regla --}}
        @if($type === 'unit_occupancy')
            <div class="grid grid-cols-2 gap-4">
                <flux:field>
                    <flux:label>Mínimo de Habitantes</flux:label>
                    <flux:input type="number" wire:model="conditions.min_occupants" placeholder="Ej: 1" min="0" />
                    <flux:description>Rango mínimo de habitantes para aplicar esta regla</flux:description>
                </flux:field>
                <flux:field>
                    <flux:label>Máximo de Habitantes</flux:label>
                    <flux:input type="number" wire:model="conditions.max_occupants" placeholder="Ej: 4" min="0" />
                    <flux:description>Rango máximo de habitantes para aplicar esta regla</flux:description>
                </flux:field>
            </div>
        @elseif($type === 'pool_weekly_guests')
            <flux:field>
                <flux:label>Días de la Semana</flux:label>
                <div class="flex flex-wrap gap-2">
                    @foreach(['Domingo' => 0, 'Lunes' => 1, 'Martes' => 2, 'Miércoles' => 3, 'Jueves' => 4, 'Viernes' => 5, 'Sábado' => 6] as $dayName => $dayValue)
                        <label class="flex items-center gap-2">
                            <input type="checkbox" wire:model="conditions.days_of_week" value="{{ $dayValue }}" class="rounded">
                            <span>{{ $dayName }}</span>
                        </label>
                    @endforeach
                </div>
                <flux:description>Seleccione los días de la semana en que aplica esta regla</flux:description>
            </flux:field>
        @endif

        {{-- Límites según el tipo de regla --}}
        @if($type === 'unit_occupancy')
            <div class="p-4 bg-blue-50 dark:bg-blue-950/30 border-2 border-blue-400 rounded-lg mb-6">
                <div class="font-semibold mb-2 text-lg">🏠 Configuración de Límite de Residentes por Ambientes</div>
                <div class="text-sm text-gray-600 dark:text-gray-400">
                    Define el límite máximo de residentes para cada cantidad de ambientes de forma individual.
                </div>
            </div>

            <div class="bg-white dark:bg-zinc-900 border-2 border-zinc-300 dark:border-zinc-600 rounded-lg p-4">
                <div class="font-semibold mb-3">📊 Tabla de Límites por Cantidad de Ambientes</div>
                
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    @for($i = 1; $i <= 10; $i++)
                        <flux:field>
                            <flux:label>{{ $i }} Ambiente{{ $i > 1 ? 's' : '' }}</flux:label>
                            <flux:input 
                                type="number" 
                                wire:model="limits.max_residents_by_rooms.{{ $i }}" 
                                placeholder="Ej: {{ $i * 2 }}" 
                                min="0" 
                                max="50"
                            />
                        </flux:field>
                    @endfor
                </div>

                <div class="mt-4 p-3 bg-blue-50 dark:bg-blue-950/30 rounded text-sm">
                    <strong>💡 Ejemplo:</strong> Si configuras "1 Ambiente = 2" y "2 Ambientes = 5", entonces:
                    <ul class="list-disc list-inside mt-2 space-y-1">
                        <li>Una unidad de 1 ambiente podrá tener máximo <strong>2 residentes</strong></li>
                        <li>Una unidad de 2 ambientes podrá tener máximo <strong>5 residentes</strong></li>
                    </ul>
                </div>
            </div>

            <div class="mt-4 text-sm text-gray-500 p-3 bg-zinc-100 dark:bg-zinc-800 rounded">
                <strong>Nota:</strong> Si no configuras un valor para cierta cantidad de ambientes, esa unidad no tendrá límite automático.
            </div>
        @elseif($type === 'pool_weekly_guests')
            <flux:field>
                <flux:label>Máximo de Invitados <span class="text-red-500">*</span></flux:label>
                <flux:input type="number" wire:model="limits.max_guests" placeholder="Ej: 2" min="0" required />
                <flux:error name="limits.max_guests" />
            </flux:field>
        @elseif($type === 'pool_monthly_guests')
            <flux:field>
                <flux:label>Máximo de Invitados por Mes <span class="text-red-500">*</span></flux:label>
                <flux:input type="number" wire:model="limits.max_guests_per_month" placeholder="Ej: 10" min="0" required />
                <flux:error name="limits.max_guests_per_month" />
            </flux:field>
        @endif

        <flux:field>
            <flux:label>Mensaje Personalizado</flux:label>
            <flux:textarea wire:model="limits.message" placeholder="Mensaje a mostrar cuando se viola la regla" rows="2" />
            <flux:description>Opcional: mensaje personalizado para cuando se exceda el límite</flux:description>
        </flux:field>

        <div class="grid grid-cols-2 gap-4">
            <flux:field>
                <flux:label>Fecha de Inicio</flux:label>
                <flux:input type="date" wire:model="valid_from" />
                <flux:error name="valid_from" />
            </flux:field>

            <flux:field>
                <flux:label>Fecha de Fin</flux:label>
                <flux:input type="date" wire:model="valid_to" />
                <flux:error name="valid_to" />
                <flux:description>Dejar vacío para regla permanente</flux:description>
            </flux:field>
        </div>

        <div class="grid grid-cols-2 gap-4">
            <flux:field>
                <flux:label>Prioridad <span class="text-red-500">*</span></flux:label>
                <flux:input type="number" wire:model="priority" placeholder="0-100" min="0" max="100" required />
                <flux:error name="priority" />
                <flux:description>Mayor número = mayor prioridad (0-100)</flux:description>
            </flux:field>

            <flux:field>
                <flux:label>Estado</flux:label>
                <flux:checkbox wire:model="is_active">Regla activa</flux:checkbox>
                <flux:error name="is_active" />
            </flux:field>
        </div>

        <flux:field>
            <flux:label>Notas</flux:label>
            <flux:textarea wire:model="notes" placeholder="Notas adicionales sobre la regla" rows="3" />
            <flux:error name="notes" />
        </flux:field>

        <flux:field>
            <flux:label>Documento de Reglamento (PDF)</flux:label>
            
            @if($rule->document_path && !$removeDocument)
                <div class="mb-3 p-3 rounded-lg border border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-red-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M4 4a2 2 0 012-2h4.586A2 2 0 0112 2.586L15.414 6A2 2 0 0116 7.414V16a2 2 0 01-2 2H6a2 2 0 01-2-2V4z" clip-rule="evenodd" />
                            </svg>
                            <div>
                                <div class="font-medium text-sm">Documento actual</div>
                                <div class="text-xs text-gray-500">{{ basename($rule->document_path) }}</div>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ $rule->documentUrl() }}" target="_blank" class="text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400">
                                Ver PDF
                            </a>
                            <flux:button type="button" size="sm" variant="danger" wire:click="$set('removeDocument', true)">
                                Eliminar
                            </flux:button>
                        </div>
                    </div>
                </div>
            @elseif($removeDocument)
                <div class="mb-3">
                    <flux:callout color="yellow">
                        El documento será eliminado al guardar.
                        <flux:button type="button" size="sm" variant="ghost" wire:click="$set('removeDocument', false)">
                            Cancelar
                        </flux:button>
                    </flux:callout>
                </div>
            @endif
            
            <input type="file" wire:model="document" accept=".pdf" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100" />
            <flux:error name="document" />
            <flux:description>Suba un nuevo archivo PDF para {{ $rule->document_path ? 'reemplazar' : 'agregar' }} el reglamento (máximo 10MB)</flux:description>
            @if($document)
                <div class="mt-2">
                    <flux:badge color="green">Nuevo archivo: {{ $document->getClientOriginalName() }}</flux:badge>
                </div>
            @endif
        </flux:field>

        <div class="flex gap-4">
            <flux:button type="submit" variant="primary">
                Actualizar
            </flux:button>
            <flux:button href="{{ route('admin.rules.index') }}" variant="ghost">
                Cancelar
            </flux:button>
        </div>
    </form>
</div>
