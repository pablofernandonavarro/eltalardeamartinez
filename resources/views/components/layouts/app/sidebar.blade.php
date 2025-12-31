<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="dark">
    <head>
        @include('partials.head')
    </head>
    <body class="min-h-screen bg-white dark:bg-zinc-800">
        <flux:sidebar sticky stashable class="border-e border-zinc-200 bg-zinc-50 dark:border-zinc-700 dark:bg-zinc-900">
            <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

            <a href="{{ route('dashboard') }}" class="me-5 flex items-center space-x-2 rtl:space-x-reverse" wire:navigate>
                <x-app-logo />
            </a>

            <flux:navlist variant="outline">
                <flux:navlist.group :heading="__('Platform')" class="grid">
                    <flux:navlist.item icon="home" :href="route('admin.dashboard')" :current="request()->routeIs('admin.dashboard')" wire:navigate>{{ __('Dashboard') }}</flux:navlist.item>
                </flux:navlist.group>
            <flux:navlist.group :heading="__('Gestión')" class="grid">
                <flux:navlist.item icon="users" :href="route('admin.users.index')" :current="request()->routeIs('admin.users.*')" wire:navigate>{{ __('Usuarios') }}</flux:navlist.item>
                <flux:navlist.item icon="home-modern" :href="route('admin.units.index')" :current="request()->routeIs('admin.units.*')" wire:navigate>{{ __('Unidades Funcionales') }}</flux:navlist.item>
                <flux:navlist.item icon="link" :href="route('admin.unit-users.index')" :current="request()->routeIs('admin.unit-users.*')" wire:navigate>{{ __('Asignaciones') }}</flux:navlist.item>
                <flux:navlist.item icon="user-group" :href="route('admin.residents.index')" :current="request()->routeIs('admin.residents.*')" wire:navigate>{{ __('Residentes') }}</flux:navlist.item>
                <flux:navlist.item icon="building-office" :href="route('admin.buildings.index')" :current="request()->routeIs('admin.buildings.*')" wire:navigate>{{ __('Edificios') }}</flux:navlist.item>
                <flux:navlist.item icon="currency-dollar" :href="route('admin.expenses.index')" :current="request()->routeIs('admin.expenses.*')" wire:navigate>{{ __('Expensas') }}</flux:navlist.item>
                <flux:navlist.item icon="beaker" :href="route('admin.pools.index')" :current="request()->routeIs('admin.pools.*')" wire:navigate>{{ __('Piletas') }}</flux:navlist.item>
                <flux:navlist.item icon="newspaper" :href="route('admin.news.index')" :current="request()->routeIs('admin.news.*')" wire:navigate>{{ __('Novedades') }}</flux:navlist.item>
                <flux:navlist.item icon="document-text" :href="route('admin.rules.index')" :current="request()->routeIs('admin.rules.*')" wire:navigate>{{ __('Reglas del Sistema') }}</flux:navlist.item>
            </flux:navlist.group>
            </flux:navlist>

            <flux:spacer />

            <!-- Logout Button - Visible -->
            <div class="mb-2 px-2">
                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <flux:button variant="ghost" type="submit" class="w-full justify-start" icon="arrow-right-start-on-rectangle">
                        {{ __('Log Out') }}
                    </flux:button>
                </form>
            </div>

            <!-- Desktop User Menu -->
            <flux:dropdown class="hidden lg:block" position="bottom" align="start">
                @if(auth()->user()->profilePhotoUrl())
                    <button type="button" class="flex w-full items-center gap-2 rounded-lg px-2 py-1.5 text-left hover:bg-neutral-100 dark:hover:bg-neutral-800">
                        <img src="{{ auth()->user()->profilePhotoUrl() }}" alt="{{ auth()->user()->name }}" class="h-10 w-10 rounded-full object-cover">
                        <div class="flex-1">
                            <div class="text-sm font-semibold">{{ auth()->user()->name }}</div>
                        </div>
                        <flux:icon icon="chevrons-up-down" class="size-4" />
                    </button>
                @else
                    <flux:profile
                        :name="auth()->user()->name"
                        :initials="auth()->user()->initials()"
                        icon:trailing="chevrons-up-down"
                    />
                @endif

                <flux:menu class="w-[220px]">
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    @if(auth()->user()->profilePhotoUrl())
                                        <img src="{{ auth()->user()->profilePhotoUrl() }}" alt="{{ auth()->user()->name }}" class="h-full w-full rounded-lg object-cover">
                                    @else
                                        <span
                                            class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                        >
                                            {{ auth()->user()->initials() }}
                                        </span>
                                    @endif
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:sidebar>

        <!-- Mobile User Menu -->
        <flux:header class="lg:hidden">
            <flux:sidebar.toggle class="lg:hidden" icon="bars-2" inset="left" />

            <flux:spacer />

            <flux:dropdown position="top" align="end">
                <button type="button" class="cursor-pointer">
                    @if(auth()->user()->profilePhotoUrl())
                        <img src="{{ auth()->user()->profilePhotoUrl() }}" alt="{{ auth()->user()->name }}" class="h-10 w-10 rounded-full object-cover">
                    @else
                        <flux:profile
                            :initials="auth()->user()->initials()"
                            icon-trailing="chevron-down"
                        />
                    @endif
                </button>

                <flux:menu>
                    <flux:menu.radio.group>
                        <div class="p-0 text-sm font-normal">
                            <div class="flex items-center gap-2 px-1 py-1.5 text-start text-sm">
                                <span class="relative flex h-8 w-8 shrink-0 overflow-hidden rounded-lg">
                                    @if(auth()->user()->profilePhotoUrl())
                                        <img src="{{ auth()->user()->profilePhotoUrl() }}" alt="{{ auth()->user()->name }}" class="h-full w-full rounded-lg object-cover">
                                    @else
                                        <span
                                            class="flex h-full w-full items-center justify-center rounded-lg bg-neutral-200 text-black dark:bg-neutral-700 dark:text-white"
                                        >
                                            {{ auth()->user()->initials() }}
                                        </span>
                                    @endif
                                </span>

                                <div class="grid flex-1 text-start text-sm leading-tight">
                                    <span class="truncate font-semibold">{{ auth()->user()->name }}</span>
                                    <span class="truncate text-xs">{{ auth()->user()->email }}</span>
                                </div>
                            </div>
                        </div>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <flux:menu.radio.group>
                        <flux:menu.item :href="route('profile.edit')" icon="cog" wire:navigate>{{ __('Settings') }}</flux:menu.item>
                    </flux:menu.radio.group>

                    <flux:menu.separator />

                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <flux:menu.item as="button" type="submit" icon="arrow-right-start-on-rectangle" class="w-full">
                            {{ __('Log Out') }}
                        </flux:menu.item>
                    </form>
                </flux:menu>
            </flux:dropdown>
        </flux:header>

        {{ $slot }}

        @fluxScripts
    </body>
</html>
