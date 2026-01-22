<x-layouts.portal :title="__('My Profile')">
    <div class="space-y-6">
        <div>
            <flux:heading size="xl">My Profile</flux:heading>
            <flux:text class="mt-1">Your musician profile information</flux:text>
        </div>

        <flux:callout icon="user">
            <flux:callout.heading>Coming soon</flux:callout.heading>
            <flux:callout.text>The profile view will be implemented in Phase 7.8.</flux:callout.text>
        </flux:callout>
    </div>
</x-layouts.portal>
