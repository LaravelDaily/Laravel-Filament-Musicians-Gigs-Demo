<x-filament-panels::page>
    <x-filament::section>
        <x-slot name="heading">
            Filter Assignments
        </x-slot>

        <x-slot name="description">
            Use the filters below to narrow down the assignments to export.
        </x-slot>

        {{ $this->form }}
    </x-filament::section>
</x-filament-panels::page>
