<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">
            System Settings
        </x-slot>

        <x-slot name="description">
            Configure the general settings for your application.
        </x-slot>

        {{ $this->form }}
    </x-filament::section>
</x-filament-panels::page>
