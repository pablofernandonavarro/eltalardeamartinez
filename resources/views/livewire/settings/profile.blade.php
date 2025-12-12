<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Profile')" :subheading="__('Update your name and email address')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6" enctype="multipart/form-data">
            <!-- Profile Photo -->
            <div class="space-y-4">
                <flux:field>
                    <flux:label>{{ __('Profile Photo') }}</flux:label>
                    <div class="flex items-center gap-4">
                        @if($profilePhotoUrl)
                            <img src="{{ $profilePhotoUrl }}" alt="{{ auth()->user()->name }}" class="h-20 w-20 rounded-full object-cover">
                        @else
                            <div class="flex h-20 w-20 items-center justify-center rounded-full bg-neutral-200 text-lg font-semibold text-black dark:bg-neutral-700 dark:text-white">
                                {{ auth()->user()->initials() }}
                            </div>
                        @endif
                        <div class="flex-1">
                            <flux:input wire:model="photo" type="file" accept="image/*" />
                            <flux:text class="mt-1 text-xs text-neutral-500">
                                {{ __('Upload a new profile photo. Max size: 2MB') }}
                            </flux:text>
                        </div>
                    </div>
                    @if($profilePhotoUrl)
                        <div class="mt-2">
                            <flux:button wire:click="deleteProfilePhoto" variant="ghost" size="sm" type="button">
                                {{ __('Remove Photo') }}
                            </flux:button>
                        </div>
                    @endif
                </flux:field>
            </div>

            <flux:input wire:model="name" :label="__('Name')" type="text" required autofocus autocomplete="name" />

            <div>
                <flux:input wire:model="email" :label="__('Email')" type="email" required autocomplete="email" />

                @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail &&! auth()->user()->hasVerifiedEmail())
                    <div>
                        <flux:text class="mt-4">
                            {{ __('Your email address is unverified.') }}

                            <flux:link class="text-sm cursor-pointer" wire:click.prevent="resendVerificationNotification">
                                {{ __('Click here to re-send the verification email.') }}
                            </flux:link>
                        </flux:text>

                        @if (session('status') === 'verification-link-sent')
                            <flux:text class="mt-2 font-medium !dark:text-green-400 !text-green-600">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </flux:text>
                        @endif
                    </div>
                @endif
            </div>

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full">{{ __('Save') }}</flux:button>
                </div>

                <x-action-message class="me-3" on="profile-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>

        <livewire:settings.delete-user-form />
    </x-settings.layout>
</section>
