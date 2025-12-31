<x-layouts.auth>
    <div class="flex flex-col gap-6">
        <x-auth-header :title="__('Create an account')" :description="__('Enter your details below to create your account')" />

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
                :label="__('Name')"
                :value="old('name')"
                type="text"
                required
                autofocus
                autocomplete="name"
                :placeholder="__('Full name')"
            />

            <!-- Email Address -->
            <flux:input
                name="email"
                :label="__('Email address')"
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
                :label="__('Password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Password')"
                viewable
            />

            <!-- Confirm Password -->
            <flux:input
                name="password_confirmation"
                :label="__('Confirm password')"
                type="password"
                required
                autocomplete="new-password"
                :placeholder="__('Confirm password')"
                viewable
            />

            <div class="flex items-center justify-end">
                <flux:button type="submit" variant="primary" class="w-full">
                    {{ __('Create account') }}
                </flux:button>
            </div>
        </form>

        <div class="space-x-1 rtl:space-x-reverse text-center text-sm text-zinc-600 dark:text-zinc-400">
            <span>{{ __('Already have an account?') }}</span>
            <flux:link :href="route('login')" wire:navigate>{{ __('Log in') }}</flux:link>
        </div>
    </div>
</x-layouts.auth>
