<x-layouts.portal :title="$gig->name">
    <div class="space-y-6">
        {{-- Header with back link --}}
        <div>
            <a href="{{ route('portal.dashboard') }}" class="mb-2 inline-flex items-center gap-1 text-sm text-zinc-500 hover:text-zinc-700 dark:text-zinc-400 dark:hover:text-zinc-200">
                <flux:icon.arrow-left class="size-4" />
                Back to Dashboard
            </a>
            <div class="flex flex-wrap items-start justify-between gap-4">
                <div>
                    <flux:heading size="xl">{{ $gig->name }}</flux:heading>
                    <flux:text class="mt-1">{{ $gig->date->format('l, F j, Y') }}</flux:text>
                </div>
                <div>
                    @if($assignment->status === App\Enums\AssignmentStatus::Pending)
                        <flux:badge color="warning" size="lg">Response Needed</flux:badge>
                    @elseif($assignment->status === App\Enums\AssignmentStatus::Accepted)
                        <flux:badge color="success" size="lg">Accepted</flux:badge>
                    @elseif($assignment->status === App\Enums\AssignmentStatus::Declined)
                        <flux:badge color="danger" size="lg">Declined</flux:badge>
                    @elseif($assignment->status === App\Enums\AssignmentStatus::SuboutRequested)
                        <flux:badge color="info" size="lg">Sub-out Requested</flux:badge>
                    @endif
                </div>
            </div>
        </div>

        {{-- Flash messages --}}
        @session('success')
            <flux:callout variant="success" icon="check-circle">
                <flux:callout.text>{{ $value }}</flux:callout.text>
            </flux:callout>
        @endsession

        @session('error')
            <flux:callout variant="danger" icon="exclamation-circle">
                <flux:callout.text>{{ $value }}</flux:callout.text>
            </flux:callout>
        @endsession

        {{-- Action buttons (only for upcoming gigs) --}}
        @if(!$gig->date->isPast())
            @if($assignment->status === App\Enums\AssignmentStatus::Pending)
                <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                    <flux:heading size="lg" class="mb-4">Respond to Assignment</flux:heading>
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start">
                        <form action="{{ route('portal.gigs.accept', $gig) }}" method="POST">
                            @csrf
                            <flux:button type="submit" variant="primary" icon="check">
                                Accept Gig
                            </flux:button>
                        </form>
                        <div class="flex-1">
                            <form action="{{ route('portal.gigs.decline', $gig) }}" method="POST" class="flex flex-col gap-2 sm:flex-row sm:items-end">
                                @csrf
                                <div class="flex-1">
                                    <flux:textarea name="reason" placeholder="Reason for declining (optional)" rows="1" class="resize-none" />
                                </div>
                                <flux:button type="submit" variant="danger" icon="x-mark">
                                    Decline
                                </flux:button>
                            </form>
                        </div>
                    </div>
                </div>
            @elseif($assignment->status === App\Enums\AssignmentStatus::Accepted)
                <div class="rounded-lg border border-amber-200 bg-amber-50 p-4 dark:border-amber-800 dark:bg-amber-900/20">
                    <flux:heading size="lg" class="mb-2">Need to cancel?</flux:heading>
                    <flux:text class="mb-4">If you can no longer make this gig, you can request a sub-out. An admin will be notified to find a replacement.</flux:text>
                    <form action="{{ route('portal.gigs.subout', $gig) }}" method="POST" class="space-y-3">
                        @csrf
                        <flux:textarea
                            name="reason"
                            placeholder="Please explain why you need a sub-out (required)"
                            rows="2"
                            required
                            :invalid="$errors->has('reason')"
                        />
                        @error('reason')
                            <flux:text class="text-red-600 dark:text-red-400">{{ $message }}</flux:text>
                        @enderror
                        <flux:button type="submit" variant="ghost" icon="arrow-path">
                            Request Sub-out
                        </flux:button>
                    </form>
                </div>
            @elseif($assignment->status === App\Enums\AssignmentStatus::SuboutRequested)
                <div class="rounded-lg border border-blue-200 bg-blue-50 p-4 dark:border-blue-800 dark:bg-blue-900/20">
                    <flux:heading size="lg" class="mb-2">Sub-out Requested</flux:heading>
                    <flux:text class="mb-2">Your sub-out request has been submitted. An admin will contact you soon.</flux:text>
                    @if($assignment->subout_reason)
                        <flux:text class="text-sm text-zinc-600 dark:text-zinc-400">
                            <strong>Reason:</strong> {{ $assignment->subout_reason }}
                        </flux:text>
                    @endif
                </div>
            @endif
        @endif

        <div class="grid gap-6 lg:grid-cols-2">
            {{-- Times Section --}}
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="lg" class="mb-4">Times</flux:heading>
                <dl class="space-y-3">
                    <div class="flex items-center gap-3">
                        <dt class="flex items-center gap-2 text-sm font-medium text-zinc-500 dark:text-zinc-400">
                            <flux:icon.clock class="size-5" />
                            Call Time
                        </dt>
                        <dd class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                            {{ $gig->call_time->format('g:i A') }}
                        </dd>
                    </div>
                    @if($gig->performance_time)
                        <div class="flex items-center gap-3">
                            <dt class="flex items-center gap-2 text-sm font-medium text-zinc-500 dark:text-zinc-400">
                                <flux:icon.play class="size-5" />
                                Performance Time
                            </dt>
                            <dd class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                                {{ $gig->performance_time->format('g:i A') }}
                            </dd>
                        </div>
                    @endif
                    @if($gig->end_time)
                        <div class="flex items-center gap-3">
                            <dt class="flex items-center gap-2 text-sm font-medium text-zinc-500 dark:text-zinc-400">
                                <flux:icon.stop class="size-5" />
                                End Time
                            </dt>
                            <dd class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                                {{ $gig->end_time->format('g:i A') }}
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- Venue Section --}}
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="lg" class="mb-4">Venue</flux:heading>
                <div class="space-y-2">
                    <div class="flex items-start gap-2">
                        <flux:icon.building-office-2 class="mt-0.5 size-5 shrink-0 text-zinc-500 dark:text-zinc-400" />
                        <div>
                            <div class="font-semibold text-zinc-900 dark:text-zinc-100">{{ $gig->venue_name }}</div>
                            <div class="text-sm text-zinc-600 dark:text-zinc-400">{{ $gig->venue_address }}</div>
                        </div>
                    </div>
                    <a
                        href="https://www.google.com/maps/search/?api=1&query={{ urlencode($gig->venue_address) }}"
                        target="_blank"
                        rel="noopener noreferrer"
                        class="inline-flex items-center gap-1 text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                    >
                        <flux:icon.map-pin class="size-4" />
                        View on Google Maps
                        <flux:icon.arrow-top-right-on-square class="size-3" />
                    </a>
                </div>
            </div>

            {{-- Your Assignment --}}
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="lg" class="mb-4">Your Assignment</flux:heading>
                <dl class="space-y-3">
                    @if($assignment->instrument)
                        <div class="flex items-center gap-3">
                            <dt class="flex items-center gap-2 text-sm font-medium text-zinc-500 dark:text-zinc-400">
                                <flux:icon.musical-note class="size-5" />
                                Instrument
                            </dt>
                            <dd class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                                {{ $assignment->instrument->name }}
                            </dd>
                        </div>
                    @endif
                    @if($assignment->pay_amount)
                        <div class="flex items-center gap-3">
                            <dt class="flex items-center gap-2 text-sm font-medium text-zinc-500 dark:text-zinc-400">
                                <flux:icon.currency-dollar class="size-5" />
                                Pay
                            </dt>
                            <dd class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                                ${{ number_format($assignment->pay_amount, 2) }}
                            </dd>
                        </div>
                    @endif
                    @if($assignment->notes)
                        <div>
                            <dt class="mb-1 flex items-center gap-2 text-sm font-medium text-zinc-500 dark:text-zinc-400">
                                <flux:icon.document-text class="size-5" />
                                Notes
                            </dt>
                            <dd class="text-sm text-zinc-900 dark:text-zinc-100">
                                {{ $assignment->notes }}
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>

            {{-- Details Section --}}
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="lg" class="mb-4">Details</flux:heading>
                <dl class="space-y-3">
                    @if($gig->dress_code)
                        <div>
                            <dt class="mb-1 flex items-center gap-2 text-sm font-medium text-zinc-500 dark:text-zinc-400">
                                <flux:icon.user class="size-5" />
                                Dress Code
                            </dt>
                            <dd class="text-sm text-zinc-900 dark:text-zinc-100">
                                {{ $gig->dress_code }}
                            </dd>
                        </div>
                    @endif
                    @if($gig->notes)
                        <div>
                            <dt class="mb-1 flex items-center gap-2 text-sm font-medium text-zinc-500 dark:text-zinc-400">
                                <flux:icon.clipboard-document-list class="size-5" />
                                Notes / Instructions
                            </dt>
                            <dd class="whitespace-pre-line text-sm text-zinc-900 dark:text-zinc-100">{{ $gig->notes }}</dd>
                        </div>
                    @endif
                    @if($gig->region)
                        <div class="flex items-center gap-3">
                            <dt class="flex items-center gap-2 text-sm font-medium text-zinc-500 dark:text-zinc-400">
                                <flux:icon.globe-alt class="size-5" />
                                Region
                            </dt>
                            <dd class="text-sm font-semibold text-zinc-900 dark:text-zinc-100">
                                {{ $gig->region->name }}
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>
        </div>

        {{-- Attachments --}}
        @if($gig->getMedia('attachments')->isNotEmpty())
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="lg" class="mb-4">Attachments</flux:heading>
                <ul class="space-y-2">
                    @foreach($gig->getMedia('attachments') as $media)
                        <li>
                            <a
                                href="{{ $media->getUrl() }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-2 text-sm text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300"
                            >
                                <flux:icon.document class="size-5" />
                                {{ $media->file_name }}
                                <span class="text-zinc-400">({{ number_format($media->size / 1024, 0) }} KB)</span>
                                <flux:icon.arrow-down-tray class="size-4" />
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Other Musicians --}}
        @if($otherAssignments->isNotEmpty())
            <div class="rounded-lg border border-zinc-200 bg-white p-4 dark:border-zinc-700 dark:bg-zinc-900">
                <flux:heading size="lg" class="mb-4">Other Musicians</flux:heading>
                <ul class="space-y-2">
                    @foreach($otherAssignments as $otherAssignment)
                        <li class="flex items-center gap-3">
                            <flux:avatar size="sm" name="{{ $otherAssignment->user->name }}" />
                            <div>
                                <div class="text-sm font-medium text-zinc-900 dark:text-zinc-100">
                                    {{ $otherAssignment->user->name }}
                                </div>
                                @if($otherAssignment->instrument)
                                    <div class="text-xs text-zinc-500 dark:text-zinc-400">
                                        {{ $otherAssignment->instrument->name }}
                                    </div>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
</x-layouts.portal>
