<x-layouts.portal :title="$gig->name">
    <div class="space-y-6">
        <div>
            <flux:heading size="xl">{{ $gig->name }}</flux:heading>
            <flux:text class="mt-1">{{ $gig->date->format('l, F j, Y') }}</flux:text>
        </div>

        <flux:callout icon="musical-note">
            <flux:callout.heading>Coming soon</flux:callout.heading>
            <flux:callout.text>The gig detail view will be implemented in Phase 7.3.</flux:callout.text>
        </flux:callout>
    </div>
</x-layouts.portal>
