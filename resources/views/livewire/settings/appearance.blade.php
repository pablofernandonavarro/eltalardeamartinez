<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Apariencia')" :subheading="__('Actualizá las preferencias de apariencia de tu cuenta')">
        <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
            <flux:radio value="light" icon="sun">{{ __('Claro') }}</flux:radio>
            <flux:radio value="dark" icon="moon">{{ __('Oscuro') }}</flux:radio>
            <flux:radio value="system" icon="computer-desktop">{{ __('Sistema') }}</flux:radio>
        </flux:radio.group>
        
        <flux:callout color="green" class="mt-4">
            <strong>✓ Cambios guardados automáticamente</strong> - El tema se aplica inmediatamente en todas las páginas.
        </flux:callout>
    </x-settings.layout>
</section>
