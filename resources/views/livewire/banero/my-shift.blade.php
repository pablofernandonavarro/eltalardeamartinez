<div>
    <div class="mb-6">
        <flux:heading size="xl">Mi Turno</flux:heading>
        <p class="text-sm text-gray-500 mt-1">
            Iniciá y finalizá tu turno en la pileta asignada.
        </p>
    </div>

    @if($errors->has('error'))
        <flux:callout color="red" class="mb-4">
            {{ $errors->first('error') }}
        </flux:callout>
    @endif

    @if(session('message'))
        <flux:callout color="green" class="mb-4">
            {{ session('message') }}
        </flux:callout>
    @endif

    <div class="max-w-2xl">
        @if($activeShift)
            {{-- Turno activo --}}
            <div class="p-6 border-2 border-green-500 dark:border-green-400 rounded-lg bg-green-50 dark:bg-green-900/20">
                <div class="flex items-center justify-between mb-4">
                    <div class="flex items-center gap-3">
                        <div class="h-12 w-12 rounded-full bg-green-500 flex items-center justify-center">
                            <flux:icon.check class="size-6 text-white" />
                        </div>
                        <div>
                            <flux:heading size="lg">Turno Activo</flux:heading>
                            <p class="text-sm text-gray-600 dark:text-gray-400">
                                En servicio desde {{ $activeShift->started_at->format('H:i') }}
                            </p>
                        </div>
                    </div>
                    <flux:badge color="green" size="lg">En turno</flux:badge>
                </div>

                <div class="space-y-3 mb-6">
                    <div class="flex items-center justify-between p-3 bg-white dark:bg-zinc-800 rounded">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Pileta asignada:</span>
                        <span class="font-semibold">{{ $activeShift->pool->name }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-white dark:bg-zinc-800 rounded">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Hora de inicio:</span>
                        <span class="font-semibold">{{ $activeShift->started_at->format('d/m/Y H:i') }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-white dark:bg-zinc-800 rounded">
                        <span class="text-sm font-medium text-gray-600 dark:text-gray-400">Duración:</span>
                        <span class="font-semibold">{{ $activeShift->started_at->diffForHumans(null, true) }}</span>
                    </div>
                </div>

                <div class="flex gap-3">
                    <flux:button 
                        href="{{ route('banero.pools.scanner') }}" 
                        variant="primary" 
                        icon="qr-code"
                        wire:navigate
                        class="flex-1"
                    >
                        Escanear QR
                    </flux:button>
                    
                    <flux:button 
                        wire:click="endShift" 
                        wire:confirm="¿Estás seguro de finalizar tu turno?"
                        variant="danger"
                        icon="x-mark"
                    >
                        Finalizar turno
                    </flux:button>
                </div>
            </div>
        @else
            {{-- Sin turno activo --}}
            <div class="p-6 border border-zinc-200 dark:border-zinc-700 rounded-lg">
                <div class="flex items-center gap-3 mb-6">
                    <div class="h-12 w-12 rounded-full bg-zinc-200 dark:bg-zinc-700 flex items-center justify-center">
                        <flux:icon.clock class="size-6 text-gray-500" />
                    </div>
                    <div>
                        <flux:heading size="lg">Sin turno activo</flux:heading>
                        <p class="text-sm text-gray-600 dark:text-gray-400">
                            Seleccioná una pileta para iniciar tu turno
                        </p>
                    </div>
                </div>

                <form wire:submit="startShift" class="space-y-4">
                    <flux:field>
                        <flux:label>Pileta <span class="text-red-500">*</span></flux:label>
                        <flux:select wire:model="selectedPoolId" placeholder="Seleccione una pileta">
                            <option value="">Seleccione una pileta</option>
                            @foreach($pools as $pool)
                                <option value="{{ $pool->id }}">{{ $pool->name }}</option>
                            @endforeach
                        </flux:select>
                        <flux:error name="selectedPoolId" />
                        <flux:description>
                            Solo podés tener un turno activo a la vez.
                        </flux:description>
                    </flux:field>

                    <flux:button type="submit" variant="primary" icon="play" class="w-full">
                        Iniciar turno
                    </flux:button>
                </form>
            </div>
        @endif
    </div>
</div>
