<div class="space-y-4">
    @if($isAdmin)
        <button type="button" wire:click="$toggle('showAssign')" class="btn-primary !min-h-[48px]">
            {{ $showAssign ? '− ' : '+ ' }}{{ __('app.parking_assign_household') }}
        </button>

        @if($showAssign)
            <div class="glass-card space-y-3 p-4">
                <p class="text-sm font-bold" style="color:var(--muted)">{{ __('app.parking_assign_hint') }}</p>

                <label class="login-label">{{ __('app.visitor_select_house') }}</label>
                <select wire:model="allocateHouseId" class="input-field">
                    <option value="">{{ __('app.select_option') }}</option>
                    @foreach($houses as $house)
                        <option value="{{ $house->id }}">{{ $house->house_number }}</option>
                    @endforeach
                </select>
                @error('allocateHouseId') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
                @if($houses->isEmpty())
                    <p class="text-sm text-amber-600">{{ __('app.parking_no_houses_available') }}</p>
                @endif

                <label class="login-label">{{ __('app.parking_slot_number') }}</label>
                <input wire:model="slotNumber" class="input-field" placeholder="{{ __('app.parking_slot_number') }}">
                @error('slotNumber') <p class="text-sm text-red-500">{{ $message }}</p> @enderror

                <label class="login-label">{{ __('app.visitor_vehicle') }} ({{ __('app.optional') }})</label>
                <input wire:model="vehicleNumber" class="input-field" placeholder="{{ __('app.visitor_vehicle') }}">
                @error('vehicleNumber') <p class="text-sm text-red-500">{{ $message }}</p> @enderror

                <button wire:click="assignToHousehold" wire:loading.attr="disabled" class="btn-primary">
                    <span wire:loading.remove wire:target="assignToHousehold">{{ __('app.parking_allocate') }}</span>
                    <span wire:loading wire:target="assignToHousehold">{{ __('app.processing') }}…</span>
                </button>
            </div>
        @endif
    @endif

    @forelse($slots as $slot)
        <div class="glass-card p-4" wire:key="parking-slot-{{ $slot->id }}">
        @php
            $allocation = $slot->activeAllocation;
            $isOccupied = $allocation !== null;
        @endphp
            <div class="flex items-start justify-between gap-2">
                <div>
                    <p class="text-lg font-bold">{{ $slot->slot_number }}</p>
                    @if($isOccupied)
                        <p class="text-sm font-semibold">{{ $allocation->house?->house_number }}</p>
                        @if($allocation->vehicle_number && $allocation->vehicle_number !== '—')
                            <p class="text-sm" style="color:var(--muted)">{{ $allocation->vehicle_number }}</p>
                        @endif
                    @else
                        <p class="text-sm" style="color:var(--muted)">{{ __('app.parking_available') }}</p>
                    @endif
                </div>
                <span class="chip {{ $isOccupied ? 'chip-warning' : 'chip-success' }}">
                    {{ $isOccupied ? __('app.parking_occupied') : __('app.parking_available') }}
                </span>
            </div>
            @if($isAdmin && $isOccupied)
                <button type="button" wire:click="release({{ $slot->id }})" wire:confirm="{{ __('app.parking_release_confirm') }}" class="btn-ghost mt-3 w-full text-sm">
                    {{ __('app.parking_release') }}
                </button>
            @endif
        </div>
    @empty
        <div class="glass-card py-12 text-center">
            <x-ui.icon name="parking" class="mx-auto h-12 w-12 opacity-30" style="color:var(--primary)" />
            <p class="mt-3 font-semibold" style="color:var(--muted)">{{ __('app.parking_empty') }}</p>
            @if($isAdmin)
                <p class="mt-1 text-sm" style="color:var(--muted)">{{ __('app.parking_empty_hint') }}</p>
            @endif
        </div>
    @endforelse
</div>
