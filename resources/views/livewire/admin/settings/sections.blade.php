<div>
    <div class="mb-6">
        <flux:heading size="xl">Secciones del Portal Residente</flux:heading>
        <p class="text-sm text-zinc-500 mt-1">Habilitá o deshabilitá las secciones que ven los residentes en su menú lateral.</p>
    </div>

    @if(session('message'))
        <flux:callout color="green" class="mb-4" icon="check-circle">
            {{ session('message') }}
        </flux:callout>
    @endif

    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 divide-y divide-zinc-200 dark:divide-zinc-700">
        @foreach($sections as $key => $section)
            <div class="flex items-center justify-between px-5 py-4">
                <div class="flex items-center gap-3">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-zinc-100 dark:bg-zinc-800">
                        <flux:icon.{{ $section['icon'] }} class="size-5 text-zinc-600 dark:text-zinc-400" />
                    </div>
                    <div>
                        <p class="text-sm font-medium text-zinc-900 dark:text-zinc-100">{{ $section['label'] }}</p>
                        <p class="text-xs text-zinc-500 dark:text-zinc-400 mt-0.5">
                            {{ $section['enabled'] ? 'Visible para los residentes' : 'Oculta para los residentes' }}
                        </p>
                    </div>
                </div>
                <flux:switch
                    wire:click="toggle('{{ $key }}')"
                    :checked="$section['enabled']"
                    wire:loading.attr="disabled"
                />
            </div>
        @endforeach
    </div>
</div>
