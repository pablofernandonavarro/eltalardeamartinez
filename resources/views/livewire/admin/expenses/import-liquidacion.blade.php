<div>
    <div class="mb-6 flex gap-4 items-center justify-between">
        <div>
            <flux:heading size="xl">Importar Liquidación de Expensas</flux:heading>
            <p class="text-sm text-zinc-500 mt-1">Subí el PDF mensual generado por iData / MisExpensas GCBA para importar las expensas del período.</p>
        </div>
        <flux:button href="{{ route('admin.expenses.index') }}" variant="ghost" icon="arrow-left">
            Volver a Expensas
        </flux:button>
    </div>

    {{-- Mensajes de resultado --}}
    @if($successMessage)
        <div class="mb-4 rounded-xl border border-green-200 dark:border-green-800 bg-green-50 dark:bg-green-950 p-4">
            <div class="flex gap-3">
                <flux:icon.check-circle class="size-5 text-green-600 dark:text-green-400 shrink-0 mt-0.5" />
                <div>
                    <p class="font-medium text-green-800 dark:text-green-200 mb-1">Importación exitosa</p>
                    <pre class="text-xs text-green-700 dark:text-green-300 whitespace-pre-wrap">{{ $successMessage }}</pre>
                </div>
            </div>
        </div>
    @endif

    @if($errorMessage)
        <div class="mb-4 rounded-xl border border-red-200 dark:border-red-800 bg-red-50 dark:bg-red-950 p-4">
            <div class="flex gap-3">
                <flux:icon.exclamation-triangle class="size-5 text-red-600 dark:text-red-400 shrink-0 mt-0.5" />
                <div>
                    <p class="font-medium text-red-800 dark:text-red-200 mb-1">Error</p>
                    <p class="text-sm text-red-700 dark:text-red-300">{{ $errorMessage }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">

        {{-- Panel izquierdo: configuración --}}
        <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6">
            <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Configuración de importación</h2>

            <div class="space-y-5">
                <flux:field>
                    <flux:label>Archivo PDF de liquidación</flux:label>
                    <flux:description>Máximo 20 MB. Formato generado por iData / MisExpensas GCBA.</flux:description>
                    <input
                        type="file"
                        wire:model="pdf"
                        accept="application/pdf"
                        class="mt-1 block w-full text-sm text-zinc-700 dark:text-zinc-300
                               file:mr-4 file:py-2 file:px-4
                               file:rounded-lg file:border-0
                               file:text-sm file:font-medium
                               file:bg-zinc-100 file:text-zinc-700
                               dark:file:bg-zinc-800 dark:file:text-zinc-300
                               hover:file:bg-zinc-200 dark:hover:file:bg-zinc-700
                               cursor-pointer"
                    />
                    @error('pdf')
                        <flux:error>{{ $message }}</flux:error>
                    @enderror
                </flux:field>

                <div class="space-y-3 pt-1">
                    <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300">Opciones</p>

                    <label class="flex items-start gap-3 cursor-pointer">
                        <flux:checkbox wire:model.live="importUnits" />
                        <div>
                            <span class="text-sm font-medium text-zinc-800 dark:text-zinc-200">Importar / actualizar unidades y propietarios</span>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Crea edificios y unidades si no existen. Actualiza propietario y coeficiente en los existentes.</p>
                        </div>
                    </label>

                    <label class="flex items-start gap-3 cursor-pointer">
                        <flux:checkbox wire:model.live="importExpenses" />
                        <div>
                            <span class="text-sm font-medium text-zinc-800 dark:text-zinc-200">Importar expensas del período</span>
                            <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">Crea las Expenses y ExpenseDetails por edificio. No duplica si el período ya fue importado.</p>
                        </div>
                    </label>
                </div>

                <div class="flex gap-3 pt-2">
                    <flux:button
                        wire:click="previsualizar"
                        wire:loading.attr="disabled"
                        variant="filled"
                        icon="magnifying-glass"
                    >
                        <span wire:loading.remove wire:target="previsualizar">Previsualizar</span>
                        <span wire:loading wire:target="previsualizar">Procesando...</span>
                    </flux:button>

                    @if($preview)
                        <flux:button
                            wire:click="importar"
                            wire:loading.attr="disabled"
                            wire:confirm="¿Confirmar la importación? Esta acción crea registros en la base de datos."
                            variant="primary"
                            icon="arrow-up-tray"
                        >
                            <span wire:loading.remove wire:target="importar">Confirmar importación</span>
                            <span wire:loading wire:target="importar">Importando...</span>
                        </flux:button>
                    @endif
                </div>
            </div>
        </div>

        {{-- Panel derecho: preview --}}
        <div>
            @if($preview)
                <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-6">
                    <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100 mb-4">Vista previa del PDF</h2>

                    <dl class="space-y-3">
                        <div class="flex justify-between py-2 border-b border-zinc-100 dark:border-zinc-800">
                            <dt class="text-sm text-zinc-500 dark:text-zinc-400">Período detectado</dt>
                            <dd class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $preview['period'] }}</dd>
                        </div>
                        <div class="flex justify-between py-2 border-b border-zinc-100 dark:border-zinc-800">
                            <dt class="text-sm text-zinc-500 dark:text-zinc-400">Período (formato BD)</dt>
                            <dd class="text-sm font-mono text-zinc-700 dark:text-zinc-300">{{ $preview['period_formatted'] }}</dd>
                        </div>
                        <div class="flex justify-between py-2 border-b border-zinc-100 dark:border-zinc-800">
                            <dt class="text-sm text-zinc-500 dark:text-zinc-400">Unidades encontradas</dt>
                            <dd class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">{{ $preview['unit_count'] }}</dd>
                        </div>
                        @if($preview['total_gastos'] > 0)
                        <div class="flex justify-between py-2 border-b border-zinc-100 dark:border-zinc-800">
                            <dt class="text-sm text-zinc-500 dark:text-zinc-400">Total gastos (referencia)</dt>
                            <dd class="text-sm font-mono text-zinc-900 dark:text-zinc-100">${{ number_format($preview['total_gastos'], 2, ',', '.') }}</dd>
                        </div>
                        @endif
                        <div class="flex justify-between py-2">
                            <dt class="text-sm text-zinc-500 dark:text-zinc-400">Expenses ya existentes para este período</dt>
                            <dd>
                                @if($preview['existing_expenses'] > 0)
                                    <flux:badge color="yellow">{{ $preview['existing_expenses'] }} ya importadas</flux:badge>
                                @else
                                    <flux:badge color="green">Ninguna</flux:badge>
                                @endif
                            </dd>
                        </div>
                    </dl>

                    @if($preview['existing_expenses'] > 0)
                        <div class="mt-4 rounded-lg bg-yellow-50 dark:bg-yellow-950 border border-yellow-200 dark:border-yellow-800 p-3">
                            <p class="text-sm text-yellow-800 dark:text-yellow-200">
                                Ya existen expenses importadas para este período. Los edificios que ya tienen registros serán saltados.
                            </p>
                        </div>
                    @endif

                    @if(!empty($preview['rubros']))
                        <div class="mt-5">
                            <p class="text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500 mb-2">Rubros detectados</p>
                            <div class="space-y-1">
                                @foreach($preview['rubros'] as $rubro)
                                    <div class="flex justify-between text-xs py-1 border-b border-zinc-50 dark:border-zinc-800">
                                        <span class="text-zinc-600 dark:text-zinc-400">{{ $rubro['number'] }}. {{ $rubro['name'] }}</span>
                                        <span class="font-mono text-zinc-800 dark:text-zinc-200">${{ number_format($rubro['total'], 2, ',', '.') }}</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @else
                <div class="rounded-xl border border-dashed border-zinc-300 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-900/50 p-10 text-center">
                    <flux:icon.document-arrow-up class="mx-auto size-12 text-zinc-300 dark:text-zinc-600 mb-3" />
                    <p class="text-zinc-500 dark:text-zinc-400 text-sm">Seleccioná un PDF y hacé clic en <strong>Previsualizar</strong> para ver los datos detectados antes de importar.</p>
                </div>
            @endif
        </div>
    </div>
</div>
