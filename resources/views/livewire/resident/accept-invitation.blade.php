<div class="min-h-screen flex items-center justify-center bg-gray-50 dark:bg-gray-900 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        @if ($tokenValid && $resident)
            <div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 dark:text-white">
                    Crear tu cuenta
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
                    Has sido invitado a crear una cuenta para acceder a la pileta
                </p>
            </div>

            <div class="mt-8 bg-white dark:bg-gray-800 py-8 px-4 shadow sm:rounded-lg sm:px-10">
                <!-- Información del residente -->
                <div class="mb-6 p-4 bg-blue-50 dark:bg-blue-900/20 rounded-lg">
                    <div class="text-sm">
                        <div class="font-medium text-gray-900 dark:text-white">{{ $resident->name }}</div>
                        <div class="text-gray-600 dark:text-gray-400">Unidad: {{ $resident->unit->full_identifier }}</div>
                    </div>
                </div>

                <form wire:submit="register" class="space-y-6">
                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Email
                        </label>
                        <div class="mt-1">
                            <input 
                                wire:model="email" 
                                id="email" 
                                type="email" 
                                required
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white sm:text-sm"
                            >
                        </div>
                        @error('email')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Contraseña -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Contraseña
                        </label>
                        <div class="mt-1">
                            <input 
                                wire:model="password" 
                                id="password" 
                                type="password" 
                                required
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white sm:text-sm"
                            >
                        </div>
                        @error('password')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">Mínimo 8 caracteres</p>
                    </div>

                    <!-- Confirmar Contraseña -->
                    <div>
                        <label for="password_confirmation" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                            Confirmar Contraseña
                        </label>
                        <div class="mt-1">
                            <input 
                                wire:model="password_confirmation" 
                                id="password_confirmation" 
                                type="password" 
                                required
                                class="appearance-none block w-full px-3 py-2 border border-gray-300 dark:border-gray-600 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-blue-500 focus:border-blue-500 dark:bg-gray-700 dark:text-white sm:text-sm"
                            >
                        </div>
                        @error('password_confirmation')
                            <p class="mt-1 text-sm text-red-600 dark:text-red-400">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Botón -->
                    <div>
                        <button 
                            type="submit"
                            class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                        >
                            Crear cuenta y acceder
                        </button>
                    </div>
                </form>
            </div>
        @else
            <div class="text-center">
                <div class="mx-auto h-12 w-12 text-red-500">
                    <svg class="h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h2 class="mt-6 text-center text-3xl font-extrabold text-gray-900 dark:text-white">
                    Invitación inválida
                </h2>
                <p class="mt-2 text-center text-sm text-gray-600 dark:text-gray-400">
                    El enlace de invitación no es válido o ya ha sido utilizado.
                </p>
                <div class="mt-6">
                    <a href="{{ route('home') }}" class="text-blue-600 hover:text-blue-500">
                        Volver al inicio
                    </a>
                </div>
            </div>
        @endif
    </div>
</div>
