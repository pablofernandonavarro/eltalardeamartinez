<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Appearance')" :subheading=" __('Update the appearance settings for your account')">
        <flux:radio.group 
            x-data="{ 
                currentTheme: $flux.appearance,
                changeTheme(newTheme) {
                    $flux.appearance = newTheme;
                    // Recargar la página después de un breve delay para que el cambio se guarde
                    setTimeout(() => {
                        window.location.reload();
                    }, 100);
                }
            }" 
            variant="segmented" 
            x-model="currentTheme"
            @change="changeTheme($event.target.value)"
        >
            <flux:radio value="light" icon="sun">{{ __('Light') }}</flux:radio>
            <flux:radio value="dark" icon="moon">{{ __('Dark') }}</flux:radio>
            <flux:radio value="system" icon="computer-desktop">{{ __('System') }}</flux:radio>
        </flux:radio.group>
        
        <flux:callout color="blue" class="mt-4">
            <strong>Nota:</strong> Al cambiar el tema, la página se recargará automáticamente para aplicar los cambios correctamente.
        </flux:callout>
    </x-settings.layout>
</section>
