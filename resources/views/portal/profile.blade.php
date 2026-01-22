<x-layouts.portal :title="__('My Profile')">
    <div class="space-y-6">
        <div>
            <flux:heading size="xl">My Profile</flux:heading>
            <flux:text class="mt-1">Your musician profile information</flux:text>
        </div>

        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Contact Information --}}
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="lg" class="mb-4">Contact Information</flux:heading>
                <dl class="space-y-3">
                    <div class="flex items-center gap-3">
                        <dt class="flex items-center gap-2 text-sm font-medium text-zinc-500 dark:text-zinc-400">
                            <flux:icon.user class="size-5" />
                            Name
                        </dt>
                        <dd class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                            {{ $user->name }}
                        </dd>
                    </div>
                    <div class="flex items-center gap-3">
                        <dt class="flex items-center gap-2 text-sm font-medium text-zinc-500 dark:text-zinc-400">
                            <flux:icon.envelope class="size-5" />
                            Email
                        </dt>
                        <dd class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                            {{ $user->email }}
                        </dd>
                    </div>
                    @if($user->phone)
                        <div class="flex items-center gap-3">
                            <dt class="flex items-center gap-2 text-sm font-medium text-zinc-500 dark:text-zinc-400">
                                <flux:icon.phone class="size-5" />
                                Phone
                            </dt>
                            <dd class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                                {{ $user->phone }}
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- Region --}}
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="lg" class="mb-4">Region</flux:heading>
                @if($user->region)
                    <div class="flex items-center gap-2 text-sm text-zinc-900 dark:text-zinc-100">
                        <flux:icon.map-pin class="size-5 text-zinc-500 dark:text-zinc-400" />
                        {{ $user->region->name }}
                    </div>
                @else
                    <flux:text class="text-zinc-500 dark:text-zinc-400">No region assigned</flux:text>
                @endif
            </div>

            {{-- Instruments --}}
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="lg" class="mb-4">Instruments</flux:heading>
                @if($user->instruments->isNotEmpty())
                    <div class="flex flex-wrap gap-2">
                        @foreach($user->instruments as $instrument)
                            <flux:badge color="primary">{{ $instrument->name }}</flux:badge>
                        @endforeach
                    </div>
                @else
                    <flux:text class="text-zinc-500 dark:text-zinc-400">No instruments assigned</flux:text>
                @endif
            </div>

            {{-- Tags --}}
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="lg" class="mb-4">Tags</flux:heading>
                @if($user->tags->isNotEmpty())
                    <div class="flex flex-wrap gap-2">
                        @foreach($user->tags as $tag)
                            <flux:badge color="zinc">{{ $tag->name }}</flux:badge>
                        @endforeach
                    </div>
                @else
                    <flux:text class="text-zinc-500 dark:text-zinc-400">No tags assigned</flux:text>
                @endif
            </div>
        </div>

        {{-- Contact Admin Notice --}}
        <flux:callout icon="information-circle">
            <flux:callout.heading>Need to update your profile?</flux:callout.heading>
            <flux:callout.text>Please contact an admin to update your profile information, instruments, or tags.</flux:callout.text>
        </flux:callout>
    </div>
</x-layouts.portal>
