<div class="space-y-4">
    @if($isAdmin)
        <button type="button" wire:click="$toggle('showForm')" class="btn-primary !min-h-[48px]">
            {{ $showForm ? '− ' : '+ ' }}{{ __('app.announcement_new') }}
        </button>

        @if($showForm)
            <div class="glass-card space-y-3 p-4">
                <input wire:model="title" class="input-field" placeholder="{{ __('app.announcement_title') }}">
                @error('title') <p class="text-sm text-red-500">{{ $message }}</p> @enderror
                <textarea wire:model="description" class="input-field min-h-[100px]" placeholder="{{ __('app.announcement_description') }}"></textarea>
                <label class="flex items-center gap-2 text-sm font-semibold">
                    <input type="checkbox" wire:model="isEmergency" class="h-5 w-5 rounded">
                    {{ __('app.announcement_emergency') }}
                </label>
                <button wire:click="publish" wire:loading.attr="disabled" class="btn-primary">
                    <span wire:loading.remove wire:target="publish">{{ __('app.announcement_publish') }}</span>
                    <span wire:loading wire:target="publish">{{ __('app.processing') }}…</span>
                </button>
            </div>
        @endif
    @endif

    @forelse($announcements as $item)
        <div class="glass-card p-4 {{ $item->is_emergency ? 'ring-2 ring-rose-500/50' : '' }}">
            <div class="flex items-start justify-between gap-2">
                <p class="font-bold">{{ $item->title }}</p>
                @if($item->is_emergency)
                    <span class="chip chip-expense shrink-0">{{ __('app.announcement_emergency') }}</span>
                @endif
            </div>
            @if($item->description)
                <p class="mt-2 text-sm leading-relaxed" style="color:var(--muted)">{{ $item->description }}</p>
            @endif
            <p class="mt-2 text-xs font-medium" style="color:var(--muted)">{{ $item->sent_at?->format('d M Y, h:i A') }}</p>
        </div>
    @empty
        <div class="glass-card py-12 text-center">
            <x-ui.icon name="megaphone" class="mx-auto h-12 w-12 opacity-30" style="color:var(--primary)" />
            <p class="mt-3 font-semibold" style="color:var(--muted)">{{ __('app.announcement_empty') }}</p>
        </div>
    @endforelse

    <div class="py-2">{{ $announcements->links() }}</div>
</div>
