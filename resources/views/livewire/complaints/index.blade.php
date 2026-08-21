<div class="space-y-4">
    <div class="glass-card animate-fade-in-up p-5" data-animate>
        <h3 class="mb-4 flex items-center gap-2 text-lg font-bold">
            <span class="flex h-8 w-8 items-center justify-center rounded-xl text-white" style="background:var(--gradient)">
                <x-ui.icon name="plus" class="h-5 w-5" />
            </span>
            {{ __('app.complaint_new') }}
        </h3>
        <div class="space-y-4">
            <x-ui.searchable-select
                wire:model="category"
                :label="__('app.complaint_category')"
                :options="collect($categories)->map(fn ($cat) => ['value' => $cat, 'label' => __('app.complaint_category_'.$cat)])->values()->all()"
            />
            <div>
                <label class="mb-2 block text-sm font-semibold" style="color:var(--muted)">{{ __('app.complaint_title') }}</label>
                <input wire:model="title" class="input-field" placeholder="{{ __('app.complaint_title_placeholder') }}">
                @error('title') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-semibold" style="color:var(--muted)">{{ __('app.complaint_description') }}</label>
                <textarea wire:model="description" class="input-field min-h-[100px] resize-none" rows="3" placeholder="{{ __('app.complaint_description_placeholder') }}"></textarea>
                @error('description') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-2 block text-sm font-semibold" style="color:var(--muted)">{{ __('app.complaint_photo') }}</label>
                <input type="file" wire:model="photo" accept="image/jpeg,image/png,image/webp" class="input-field !py-2 file:mr-3 file:rounded-lg file:border-0 file:bg-[var(--primary)] file:px-3 file:py-1.5 file:text-sm file:font-semibold file:text-white">
                @error('photo') <p class="mt-1 text-sm text-red-500">{{ $message }}</p> @enderror
                <div wire:loading wire:target="photo" class="mt-2 text-sm" style="color:var(--muted)">{{ __('app.complaint_photo_uploading') }}…</div>
                @if($photo)
                    <img src="{{ $photo->temporaryUrl() }}" alt="" class="mt-3 max-h-40 w-full rounded-2xl object-cover">
                @endif
            </div>
            <button wire:click="submit" wire:loading.attr="disabled" class="btn-primary">
                <span wire:loading.remove wire:target="submit">{{ __('app.complaint_submit') }}</span>
                <span wire:loading wire:target="submit">{{ __('app.processing') }}…</span>
            </button>
        </div>
    </div>

    <h3 class="px-1 text-sm font-bold uppercase tracking-wider animate-fade-in-up" style="color:var(--muted)">{{ __('app.complaint_yours') }}</h3>

    @forelse($complaints as $index => $c)
        @php
            $status = $c->status instanceof \App\Enums\ComplaintStatus ? $c->status->value : (string) $c->status;
            $statusClass = match($status) {
                'resolved' => 'chip-success',
                'in_progress' => 'chip-info',
                default => 'chip-warning',
            };
            $statusLabel = match($status) {
                'resolved' => __('app.complaint_status_resolved'),
                'in_progress' => __('app.complaint_status_in_progress'),
                default => __('app.pending'),
            };
        @endphp
        <div class="glass-card animate-fade-in-up p-4" data-animate style="animation-delay: {{ min($index * 0.05, 0.25) }}s">
            <div class="flex items-start justify-between gap-3">
                <div class="min-w-0 flex-1">
                    <p class="font-bold">{{ $c->title }}</p>
                    <p class="mt-1 text-sm font-medium" style="color:var(--muted)">{{ $c->complaint_number }}</p>
                    @if(!auth()->user()->hasRole('member') && $c->user)
                        <p class="mt-1 text-sm" style="color:var(--muted)">{{ $c->user->name }}</p>
                    @endif
                    <span class="chip chip-neutral mt-2">{{ __('app.complaint_category_'.$c->category) }}</span>
                </div>
                <span class="chip {{ $statusClass }} shrink-0">{{ $statusLabel }}</span>
            </div>
            <p class="mt-3 text-sm leading-relaxed" style="color:var(--muted)">{{ str($c->description)->limit(160) }}</p>
            @foreach($c->attachments as $attachment)
                <a href="{{ $attachment->url }}" target="_blank" rel="noopener" class="mt-3 block">
                    <img src="{{ $attachment->url }}" alt="" class="max-h-48 w-full rounded-2xl object-cover">
                </a>
            @endforeach
            @if($c->admin_remarks)
                <p class="mt-3 rounded-xl bg-black/5 px-3 py-2 text-sm dark:bg-white/5">
                    <span class="font-semibold">{{ __('app.complaint_admin_remarks') }}:</span> {{ $c->admin_remarks }}
                </p>
            @endif
        </div>
    @empty
        <div class="glass-card py-12 text-center">
            <x-ui.icon name="clipboard" class="mx-auto h-12 w-12 opacity-30" style="color:var(--primary)" />
            <p class="mt-3 font-semibold" style="color:var(--muted)">{{ __('app.complaint_empty') }}</p>
        </div>
    @endforelse

    <div class="py-2">{{ $complaints->links() }}</div>
</div>
