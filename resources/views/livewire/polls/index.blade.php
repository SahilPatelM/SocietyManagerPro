<div class="space-y-4">
    @if($isAdmin)
        <button type="button" wire:click="$toggle('showForm')" class="btn-primary !min-h-[48px]">
            {{ $showForm ? '− ' : '+ ' }}{{ __('app.poll_new') }}
        </button>

        @if($showForm)
            <div class="glass-card space-y-3 p-4">
                <input wire:model="title" class="input-field" placeholder="{{ __('app.poll_title') }}">
                @error('title') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
                <textarea wire:model="description" class="input-field min-h-[80px]" placeholder="{{ __('app.poll_description') }}"></textarea>
                <p class="text-xs font-bold uppercase tracking-wide" style="color:var(--muted)">{{ __('app.poll_options_label') }}</p>
                <input wire:model="option1" class="input-field" placeholder="{{ __('app.poll_option') }} 1">
                <input wire:model="option2" class="input-field" placeholder="{{ __('app.poll_option') }} 2">
                <input wire:model="option3" class="input-field" placeholder="{{ __('app.poll_option') }} 3 ({{ __('app.optional') }})">
                <input wire:model="option4" class="input-field" placeholder="{{ __('app.poll_option') }} 4 ({{ __('app.optional') }})">
                @error('option1') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
                <label class="text-xs font-semibold" style="color:var(--muted)">{{ __('app.poll_ends_at') }}</label>
                <input type="date" wire:model="endsAt" class="input-field">
                @error('endsAt') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
                <button wire:click="publish" wire:loading.attr="disabled" class="btn-primary">
                    <span wire:loading.remove wire:target="publish">{{ __('app.poll_publish') }}</span>
                    <span wire:loading wire:target="publish">{{ __('app.processing') }}…</span>
                </button>
            </div>
        @endif
    @endif

    @forelse($polls as $poll)
        @php
            $hasVoted = isset($userVotes[$poll->id]);
            $showResults = $hasVoted || ! $poll->isOpen();
            $total = max(1, $poll->totalVotes());
        @endphp
        <div class="glass-card space-y-3 p-4">
            <div class="flex items-start justify-between gap-2">
                <p class="font-bold leading-snug">{{ $poll->title }}</p>
                @if($poll->isOpen())
                    <span class="chip chip-income shrink-0 text-xs">{{ __('app.poll_active') }}</span>
                @else
                    <span class="chip shrink-0 text-xs" style="color:var(--muted)">{{ __('app.poll_closed_label') }}</span>
                @endif
            </div>
            @if($poll->description)
                <p class="text-sm leading-relaxed" style="color:var(--muted)">{{ $poll->description }}</p>
            @endif
            <p class="text-xs font-medium" style="color:var(--muted)">
                {{ $poll->published_at?->format('d M Y') }}
                @if($poll->ends_at)
                    · {{ __('app.poll_ends') }} {{ $poll->ends_at->format('d M Y') }}
                @endif
                · {{ $poll->totalVotes() }} {{ __('app.poll_votes') }}
            </p>

            <div class="space-y-2">
                @foreach($poll->options as $option)
                    @php
                        $pct = $showResults ? round(($option->votes_count / $total) * 100) : 0;
                        $isUserChoice = $hasVoted && ($userVotes[$poll->id] ?? null) == $option->id;
                    @endphp
                    @if($showResults)
                        <div class="poll-result-row {{ $isUserChoice ? 'poll-result-row--chosen' : '' }}">
                            <div class="flex items-center justify-between gap-2 text-sm">
                                <span class="font-semibold">{{ $option->label }}</span>
                                <span class="text-xs font-bold tabular-nums" style="color:var(--muted)">{{ $pct }}%</span>
                            </div>
                            <div class="poll-result-bar mt-1.5">
                                <div class="poll-result-fill" style="width: {{ $pct }}%"></div>
                            </div>
                            @if($isUserChoice)
                                <p class="mt-1 text-xs font-semibold" style="color:var(--primary)">{{ __('app.poll_your_vote') }}</p>
                            @endif
                        </div>
                    @else
                        <button
                            type="button"
                            wire:click="vote({{ $poll->id }}, {{ $option->id }})"
                            wire:loading.attr="disabled"
                            wire:target="vote({{ $poll->id }}, {{ $option->id }})"
                            class="poll-vote-btn w-full text-left"
                        >
                            <span wire:loading.remove wire:target="vote({{ $poll->id }}, {{ $option->id }})">{{ $option->label }}</span>
                            <span wire:loading wire:target="vote({{ $poll->id }}, {{ $option->id }})">{{ __('app.processing') }}…</span>
                        </button>
                    @endif
                @endforeach
            </div>

            @if($hasVoted && $poll->isOpen())
                <p class="text-xs font-medium" style="color:var(--primary)">{{ __('app.poll_thanks') }}</p>
            @endif

            @if($isAdmin && $poll->isOpen())
                <button type="button" wire:click="closePoll({{ $poll->id }})" wire:confirm="{{ __('app.poll_close_confirm') }}" class="text-sm font-semibold underline" style="color:var(--muted)">
                    {{ __('app.poll_close') }}
                </button>
            @endif
        </div>
    @empty
        <div class="glass-card py-12 text-center">
            <x-ui.icon name="poll" class="mx-auto h-12 w-12 opacity-30" style="color:var(--primary)" />
            <p class="mt-3 font-semibold" style="color:var(--muted)">{{ __('app.poll_empty') }}</p>
        </div>
    @endforelse

    <div class="py-2">{{ $polls->links() }}</div>
</div>
