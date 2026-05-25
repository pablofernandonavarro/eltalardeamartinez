<x-layouts.auth>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Crear una cuenta')" :description="__('Completá tus datos para registrarte')" />

        <!-- Session Status -->
        <x-auth-session-status class="text-center" :status="session('status')" />

        <flux:callout color="blue" class="text-sm">
            {{ __('Después de registrarte, el administrador asignará tu rol y aprobará tu cuenta. Recibirás una notificación cuando puedas acceder al sistema.') }}
        </flux:callout>

        <form method="POST" action="{{ route('register.store') }}" class="flex flex-col gap-6">
            @csrf

            <!-- Name -->
            <flux:input
                name="name"
                :label="__('Nombre completo')"
                :value="old('name')"
                type="text"
                required
                autofocus
                autocomplete="name"
                :placeholder="__('Tu nombre completo')"
            />

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Correo electrónico')"
                :value="old('email')"
                type="email"
                required
                autocomplete="email"
                placeholder="email@example.com"
            />

            <!-- Unidad Funcional -->
            <flux:field>
                <flux:label>{{ __('Unidad Funcional') }} *</flux:label>
                <flux:select name="requested_unit_id" required>
                    <option value="">{{ __('Seleccione su unidad') }}</option>
                    @foreach(\App\Models\Unit::with('building')->orderBy('number')->get() as $unit)
                        <option value="{{ $unit->id }}" {{ old('requested_unit_id') == $unit->id ? 'selected' : '' }}>
                            {{ $unit->full_identifier }}
                        </option>
                    @endforeach
                </flux:select>
                <flux:description>
                    {{ __('Seleccione la unidad funcional a la que pertenece. Esta información será verificada por el administrador.') }}
                </flux:description>
                @error('requested_unit_id')
                    <flux:error>{{ $message }}</flux:error>
                @enderror
            </flux:field>

            <!-- Password -->
            <flux:input
                name="password"
                :label="__('Contraseña')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Contraseña')"
                viewable
            />

            <!-- Confirm Password -->
            <flux:input
                name="password_confirmation"
                :label="__('Confirmar contraseña')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Repetí tu contraseña')"
                viewable
            />

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full">
                    {{ __('Crear cuenta') }}
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ __('¿Ya tenés una cuenta?') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('Iniciá sesión') }}</flux:link>
        </div>
    </div>
</x-layouts.auth>
