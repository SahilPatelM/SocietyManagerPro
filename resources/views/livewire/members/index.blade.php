<div class="space-y-4">
    <div class="search-box animate-fade-in-up" data-animate>
        <span class="search-icon"><x-ui.icon name="search" class="h-5 w-5" /></span>
        <input type="search" wire:model.live.debounce.300ms="search" class="input-field" placeholder="Search name, house, mobile...">
    </div>

    <div wire:loading wire:target="search" class="skeleton h-20 w-full"></div>

    <div class="space-y-3">
        @forelse($members as $index => $member)
            <div class="list-item animate-fade-in-up" data-animate style="animation-delay: {{ min($index * 0.05, 0.3) }}s">
                <div class="avatar h-14 w-14 text-lg">
                    {{ strtoupper(substr($member->name, 0, 1)) }}
                </div>
                <div class="min-w-0 flex-1">
                    <p class="truncate text-lg font-bold">{{ $member->name }}</p>
                    <p class="mt-0.5 flex flex-wrap items-center gap-2 text-sm font-medium" style="color:var(--muted)">
                        <span class="inline-flex items-center gap-1">
                            <x-ui.icon name="building" class="h-4 w-4" />
                            {{ $member->house?->house_number ?? '—' }}
                        </span>
                        <span>·</span>
                        <span>{{ $member->mobile }}</span>
                    </p>
                </div>
                <span class="chip {{ $member->status === 'active' ? 'chip-success' : 'chip-neutral' }}">
                    {{ ucfirst($member->status) }}
                </span>
            </div>
        @empty
            <div class="glass-card py-16 text-center animate-scale-in" data-animate>
                <x-ui.icon name="users" class="mx-auto h-14 w-14 opacity-30" style="color:var(--primary)" />
                <p class="mt-4 text-lg font-semibold">No members found</p>
                <p class="mt-1 text-sm" style="color:var(--muted)">Try a different search term</p>
            </div>
        @endforelse
    </div>

    <div class="py-2">{{ $members->links() }}</div>
</div>
