<div class="space-y-4">
    <div class="grid grid-cols-3 gap-3">
        <div class="glass-card p-3 text-center">
            <p class="text-2xl font-extrabold">{{ $counts['total'] }}</p>
            <p class="text-xs font-semibold" style="color:var(--muted)">{{ __('app.houses_total') }}</p>
        </div>
        <div class="glass-card p-3 text-center">
            <p class="text-2xl font-extrabold text-emerald-600">{{ $counts['occupied'] }}</p>
            <p class="text-xs font-semibold" style="color:var(--muted)">{{ __('app.houses_occupied') }}</p>
        </div>
        <div class="glass-card p-3 text-center">
            <p class="text-2xl font-extrabold text-amber-600">{{ $counts['vacant'] }}</p>
            <p class="text-xs font-semibold" style="color:var(--muted)">{{ __('app.houses_vacant') }}</p>
        </div>
    </div>

    <div class="search-box">
        <span class="search-icon"><x-ui.icon name="search" class="h-5 w-5" /></span>
        <input type="search" wire:model.live.debounce.300ms="search" class="input-field" placeholder="{{ __('app.search_house_member') }}">
    </div>

    @forelse($houses as $house)
        <div class="glass-card p-4">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <p class="text-lg font-bold">{{ $house->house_number }}</p>
                    <p class="text-sm" style="color:var(--muted)">{{ $house->block?->name ?? '—' }} · {{ $house->owner?->name ?? __('app.houses_no_owner') }}</p>
                </div>
                <span class="chip {{ $house->status === 'occupied' ? 'chip-success' : 'chip-neutral' }}">{{ ucfirst($house->status) }}</span>
            </div>
            @if($house->outstanding_amount > 0)
                <p class="mt-2 text-sm font-semibold text-amber-600">{{ __('app.balance_due') }}: ₹{{ number_format($house->outstanding_amount) }}</p>
            @endif
        </div>
    @empty
        <div class="glass-card py-12 text-center">
            <x-ui.icon name="building" class="mx-auto h-12 w-12 opacity-30" style="color:var(--primary)" />
            <p class="mt-3 font-semibold" style="color:var(--muted)">{{ __('app.houses_empty') }}</p>
        </div>
    @endforelse

    <div class="py-2">{{ $houses->links() }}</div>
</div>
