<div>
    <div class="mb-6 flex gap-4 items-center justify-between">
        <flux:heading size="xl">Gestión de Usuarios</flux:heading>
        <flux:button href="{{ route('admin.users.create') }}" variant="primary">
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

    <div class="mb-6 flex gap-4">
        <flux:field>
            <flux:label>Rol</flux:label>
            <flux:select wire:model.live="role" placeholder="Todos los roles">
                <option value="">Todos los roles</option>
                <option value="null">Sin rol asignado</option>
                <option value="{{ \App\Role::Admin->value }}">{{ \App\Role::Admin->label() }}</option>
                <option value="{{ \App\Role::Propietario->value }}">{{ \App\Role::Propietario->label() }}</option>
                <option value="{{ \App\Role::Inquilino->value }}">{{ \App\Role::Inquilino->label() }}</option>
            </flux:select>
        </flux:field>

        <flux:field>
            <flux:label>Estado de Aprobación</flux:label>
            <flux:select wire:model.live="approvalStatus" placeholder="Todos los estados">
                <option value="">Todos los estados</option>
                <option value="approved">Aprobados</option>
                <option value="pending">Pendientes</option>
            </flux:select>
        </flux:field>

        <flux:field>
            <flux:label>Buscar</flux:label>
            <flux:input wire:model.live.debounce.300ms="search" placeholder="Buscar por nombre o email..." />
        </flux:field>

        <div class="flex items-end">
            <flux:button wire:click="resetFilters" variant="ghost">Limpiar</flux:button>
        </div>
    </div>

    <div class="overflow-x-auto">
        <table class="w-full">
            <thead>
                <tr class="border-b">
                    <th class="text-left p-2">Nombre</th>
                    <th class="text-left p-2">Email</th>
                    <th class="text-left p-2">Rol</th>
                    <th class="text-left p-2">Unidades Funcionales</th>
                    <th class="text-left p-2">Email Verificado</th>
                    <th class="text-left p-2">Estado</th>
                    <th class="text-center p-2">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr class="border-b hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors">
                        <td class="p-2 font-medium">{{ $user->name }}</td>
                        <td class="p-2">{{ $user->email }}</td>
                        <td class="p-2">
                            @if($user->role)
                                <flux:badge color="{{ $user->role === \App\Role::Admin ? 'red' : ($user->role === \App\Role::Propietario ? 'blue' : 'green') }}">
                                    {{ $user->role?->label() ?? 'Sin rol' }}
                                </flux:badge>
                            @else
                                <flux:badge color="yellow">Sin rol asignado</flux:badge>
                            @endif
                        </td>
                        <td class="p-2">
                            @if($user->currentUnitUsers->count() > 0)
                                <div class="flex flex-col gap-1">
                                    @foreach($user->currentUnitUsers as $unitUser)
                                        <div class="flex items-center gap-2">
                                            <span class="text-sm">
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
                            @else
                                <span class="text-gray-400 text-sm">Sin unidades</span>
                            @endif
                        </td>
                        <td class="p-2">
                            @if($user->email_verified_at)
                                <flux:badge color="green">Verificado</flux:badge>
                            @else
                                <flux:badge color="yellow">Pendiente</flux:badge>
                            @endif
                        </td>
                        <td class="p-2">
                            @if($user->isApproved())
                                <flux:badge color="green">Aprobado</flux:badge>
                            @else
                                <flux:badge color="red">Pendiente</flux:badge>
                            @endif
                        </td>
                        <td class="p-2">
                            <div class="flex gap-2 justify-center flex-wrap">
                                @if(!$user->role)
                                    <flux:button href="{{ route('admin.users.edit', $user) }}" variant="ghost" size="sm" color="blue">
                                        Asignar Rol
                                    </flux:button>
                                @endif
                                @if(!$user->isApproved() && !$user->isAdmin() && $user->role)
                                    <flux:button wire:click="approve({{ $user->id }})" 
                                        wire:confirm="¿Está seguro de aprobar este usuario?"
                                        variant="ghost" 
                                        size="sm"
                                        color="green">
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
                        <td colspan="7" class="p-8 text-center text-gray-500">
                            No se encontraron usuarios
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $users->links() }}
    </div>
</div>
