<x-layouts.portal :title="__('Past Gigs')">
    <div class="space-y-6">
        <div>
            <flux:heading size="xl">Past Gigs</flux:heading>
            <flux:text class="mt-1">Your completed gig assignments</flux:text>
        </div>

        @if($assignments->isEmpty())
            <flux:callout icon="calendar">
                <flux:callout.heading>No past gigs</flux:callout.heading>
                <flux:callout.text>You don't have any past gig assignments yet.</flux:callout.text>
            </flux:callout>
        @else
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                @foreach($assignments as $assignment)
                    <a href="{{ route('portal.gigs.show', $assignment->gig) }}" class="block rounded-lg border border-zinc-200 bg-white p-4 shadow-sm transition hover:border-zinc-300 hover:shadow dark:border-zinc-700 dark:bg-zinc-900 dark:hover:border-zinc-600">
                        <div class="mb-3 flex items-start justify-between gap-2">
                            <div>
                                <div class="text-sm font-medium text-zinc-500 dark:text-zinc-400">
                                    {{ $assignment->gig->date->format('l') }}
                                </div>
                                <div class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">
                                    {{ $assignment->gig->date->format('M j, Y') }}
                                </div>
                            </div>
                            @if($assignment->status === App\Enums\AssignmentStatus::Accepted)
                                <flux:badge color="success" size="sm">Accepted</flux:badge>
                            @elseif($assignment->status === App\Enums\AssignmentStatus::Declined)
                                <flux:badge color="danger" size="sm">Declined</flux:badge>
                            @elseif($assignment->status === App\Enums\AssignmentStatus::Pending)
                                <flux:badge color="warning" size="sm">Pending</flux:badge>
                            @elseif($assignment->status === App\Enums\AssignmentStatus::SuboutRequested)
                                <flux:badge color="info" size="sm">Sub-out</flux:badge>
                            @endif
                        </div>

                        <flux:heading size="lg" class="mb-1">{{ $assignment->gig->name }}</flux:heading>

                        <div class="space-y-1 text-sm text-zinc-600 dark:text-zinc-400">
                            <div class="flex items-center gap-2">
                                <flux:icon.map-pin class="size-4" />
                                <span class="truncate">{{ $assignment->gig->venue_name }}</span>
                            </div>
                            @if($assignment->instrument)
                                <div class="flex items-center gap-2">
                                    <flux:icon.musical-note class="size-4" />
                                    <span>{{ $assignment->instrument->name }}</span>
                                </div>
                            @endif
                        </div>
                    </a>
                @endforeach
            </div>

            @if($assignments->hasPages())
                <div class="mt-6">
                    {{ $assignments->links() }}
                </div>
            @endif
        @endif
    </div>
</x-layouts.portal>
