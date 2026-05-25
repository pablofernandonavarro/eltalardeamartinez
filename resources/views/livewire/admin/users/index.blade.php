<div>
    <div class="mb-6 flex gap-4 items-center justify-between">
        <div>
            <flux:heading size="xl">Gestión de Usuarios</flux:heading>
            <p class="text-sm text-zinc-500 mt-1">Gestioná cuentas, roles y aprobaciones de los usuarios del sistema.</p>
        </div>
        <flux:button href="{{ route('admin.users.create') }}" variant="primary" icon="plus">
            Nuevo Usuario
        </flux:button>
    </div>

    @if(session('message'))
        <flux:callout color="green" class="mb-4">
            {{ session('message') }}
        </flux:callout>
    @endif

    @if(session('error'))
        <flux:callout color="red" class="mb-4">
            {{ session('error') }}
        </flux:callout>
    @endif

    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 p-4 mb-6">
        <div class="flex flex-wrap gap-4 items-end">
            <flux:field class="min-w-[180px]">
                <flux:label>Rol</flux:label>
                <flux:select wire:model.live="role" placeholder="Todos los roles">
                    <option value="">Todos los roles</option>
                    <option value="null">Sin rol asignado</option>
                    <option value="{{ \App\Role::Admin->value }}">{{ \App\Role::Admin->label() }}</option>
                    <option value="{{ \App\Role::Banero->value }}">{{ \App\Role::Banero->label() }}</option>
                    <option value="{{ \App\Role::Propietario->value }}">{{ \App\Role::Propietario->label() }}</option>
                    <option value="{{ \App\Role::Inquilino->value }}">{{ \App\Role::Inquilino->label() }}</option>
                    <option value="{{ \App\Role::Residente->value }}">{{ \App\Role::Residente->label() }}</option>
                </flux:select>
            </flux:field>

            <flux:field class="min-w-[180px]">
                <flux:label>Estado de Aprobación</flux:label>
                <flux:select wire:model.live="approvalStatus" placeholder="Todos los estados">
                    <option value="">Todos los estados</option>
                    <option value="approved">Aprobados</option>
                    <option value="pending">Pendientes</option>
                </flux:select>
            </flux:field>

            <flux:field class="min-w-[200px] flex-1">
                <flux:label>Buscar</flux:label>
                <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre o email..." icon="magnifying-glass" />
            </flux:field>

            <div class="flex items-end">
                <flux:button wire:click="resetFilters" variant="ghost" icon="x-mark">Limpiar</flux:button>
            </div>
        </div>
    </div>

    <div class="rounded-xl border border-zinc-200 dark:border-zinc-700 bg-white dark:bg-zinc-900 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr class="border-b border-zinc-200 dark:border-zinc-700 bg-zinc-50 dark:bg-zinc-800">
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Nombre</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Email</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Rol</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Unidades Funcionales</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Email Verificado</th>
                        <th class="text-left px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Estado</th>
                        <th class="text-center px-4 py-3 text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-zinc-200 dark:divide-zinc-700">
                    @forelse($users as $user)
                        <tr class="hover:bg-zinc-50 dark:hover:bg-zinc-800 transition-colors">
                            <td class="px-4 py-3 font-medium text-zinc-900 dark:text-zinc-100">{{ $user->name }}</td>
                            <td class="px-4 py-3 text-zinc-700 dark:text-zinc-300">{{ $user->email }}</td>
                            <td class="px-4 py-3">
                            @if($user->role)
                                @php
                                    $badgeColor = match($user->role) {
                                        \App\Role::Admin => 'red',
                                        \App\Role::Banero => 'cyan',
                                        \App\Role::Propietario => 'blue',
                                        \App\Role::Inquilino => 'purple',
                                        \App\Role::Residente => 'green',
                                        default => 'zinc',
                                    };
                                @endphp
                                <flux:badge color="{{ $badgeColor }}">
                                    {{ $user->role?->label() ?? 'Sin rol' }}
                                </flux:badge>
                            @else
                                <flux:badge color="yellow">Sin rol asignado</flux:badge>
                            @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($user->currentUnitUsers->count() > 0)
                                    <div class="flex flex-col gap-1">
                                        @foreach($user->currentUnitUsers as $unitUser)
                                            <div class="flex items-center gap-2">
                                                <span class="text-sm text-zinc-900 dark:text-zinc-100">
                                                    {{ $unitUser->unit->full_identifier }}
                                                </span>
                                                @if($unitUser->is_owner ?? false)
                                                    <flux:badge size="sm" color="purple">Propietario</flux:badge>
                                                @endif
                                                @if($unitUser->is_responsible)
                                                    <flux:badge size="sm" color="blue">Responsable Pago</flux:badge>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @elseif($user->requestedUnit)
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm text-zinc-600 dark:text-zinc-400">
                                            {{ $user->requestedUnit->full_identifier }}
                                        </span>
                                        <flux:badge size="sm" color="yellow">Solicitada</flux:badge>
                                    </div>
                                @else
                                    <span class="text-zinc-400 text-sm">Sin unidades</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($user->email_verified_at)
                                    <flux:badge color="green">Verificado</flux:badge>
                                @else
                                    <flux:badge color="yellow">Pendiente</flux:badge>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($user->isApproved())
                                    <flux:badge color="green">Aprobado</flux:badge>
                                @else
                                    <flux:badge color="red">Pendiente</flux:badge>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex gap-2 justify-center flex-wrap">
                                @if(!$user->role)
                                    <flux:button href="{{ route('admin.users.edit', $user) }}" variant="primary" size="sm">
                                        Asignar Rol
                                    </flux:button>
                                @endif
                                @if(!$user->isApproved() && !$user->isAdmin() && $user->role)
                                    <flux:button wire:click="approve({{ $user->id }})" 
                                        wire:confirm="¿Está seguro de aprobar este usuario?"
                                        variant="primary" 
                                        size="sm">
                                        Aprobar
                                    </flux:button>
                                @endif
                                @if($user->isApproved() && !$user->isAdmin())
                                    <flux:button wire:click="reject({{ $user->id }})" 
                                        wire:confirm="¿Está seguro de rechazar este usuario? No podrá usar el sistema hasta ser aprobado nuevamente."
                                        variant="ghost" 
                                        size="sm"
                                        color="yellow">
                                        Rechazar
                                    </flux:button>
                                @endif
                                <flux:button href="{{ route('admin.users.edit', $user) }}" variant="ghost" size="sm">
                                    Editar
                                </flux:button>
                                    @if($user->id !== auth()->id())
                                        <flux:button wire:click="delete({{ $user->id }})"
                                            wire:confirm="¿Está seguro de eliminar este usuario? Esta acción no se puede deshacer."
                                            variant="ghost"
                                            size="sm"
                                            color="red">
                                            Eliminar
                                        </flux:button>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="px-4 py-12 text-center">
                                <flux:icon.users class="mx-auto size-10 text-zinc-300 dark:text-zinc-600 mb-3" />
                                <p class="text-zinc-500 dark:text-zinc-400">No se encontraron usuarios</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
</div>
