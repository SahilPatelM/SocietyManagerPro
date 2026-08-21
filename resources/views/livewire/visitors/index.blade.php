<div class="space-y-4">
    <button type="button" wire:click="$toggle('showForm')" class="btn-primary !min-h-[48px]">
        {{ $showForm ? '− ' : '+ ' }}{{ __('app.visitor_log_entry') }}
    </button>

    @if($showForm)
        <div class="glass-card space-y-3 p-4">
            @if($canSelectHouse)
                <x-ui.searchable-select
                    wire:model="houseId"
                    :placeholder="__('app.visitor_select_house')"
                    :options="collect($houses)->map(fn ($h) => ['value' => (string) $h->id, 'label' => $h->house_number])->values()->all()"
                />
            @endif
            <input wire:model="visitorName" class="input-field" placeholder="{{ __('app.visitor_name') }}">
            <input wire:model="mobile" class="input-field" placeholder="{{ __('app.visitor_mobile') }}">
            <input wire:model="vehicleNumber" class="input-field" placeholder="{{ __('app.visitor_vehicle') }}">
            <button wire:click="checkIn" wire:loading.attr="disabled" class="btn-primary">{{ __('app.visitor_check_in') }}</button>
        </div>
    @endif

    <div class="segmented">
        <button type="button" wire:click="$set('filter', 'inside')" class="{{ $filter === 'inside' ? 'active' : '' }}">{{ __('app.visitor_inside') }}</button>
        <button type="button" wire:click="$set('filter', 'all')" class="{{ $filter === 'all' ? 'active' : '' }}">{{ __('app.all') }}</button>
    </div>

    @forelse($visitors as $v)
        <div class="glass-card p-4">
            <div class="flex items-start justify-between gap-2">
                <div>
                    <p class="font-bold">{{ $v->visitor_name }}</p>
                    <p class="text-sm" style="color:var(--muted)">{{ $v->house?->house_number }} · {{ $v->entry_time->format('d M, h:i A') }}</p>
                    @if($v->mobile)<p class="text-sm" style="color:var(--muted)">{{ $v->mobile }}</p>@endif
                </div>
                @if(!$v->exit_time)
                    <button wire:click="checkOut({{ $v->id }})" class="chip chip-success shrink-0">{{ __('app.visitor_check_out') }}</button>
                @else
                    <span class="chip chip-neutral shrink-0">{{ __('app.visitor_left') }}</span>
                @endif
            </div>
        </div>
    @empty
        <div class="glass-card py-12 text-center">
            <x-ui.icon name="user-plus" class="mx-auto h-12 w-12 opacity-30" style="color:var(--primary)" />
            <p class="mt-3 font-semibold" style="color:var(--muted)">{{ __('app.visitor_empty') }}</p>
        </div>
    @endforelse

    <div class="py-2">{{ $visitors->links() }}</div>
</div>
