<div class="space-y-4">
    @if($isAdmin)
        <button type="button" wire:click="$toggle('showForm')" class="btn-primary !min-h-[48px]">
            {{ $showForm ? '− ' : '+ ' }}{{ __('app.document_upload') }}
        </button>

        @if($showForm)
            <div class="glass-card space-y-3 p-4">
                <input wire:model="title" class="input-field" placeholder="{{ __('app.document_title') }}">
                <x-ui.searchable-select
                    wire:model="category"
                    :options="collect(\App\Livewire\Documents\Index::CATEGORIES)->map(fn ($cat) => ['value' => $cat, 'label' => __('app.document_category_'.$cat)])->values()->all()"
                />
                <input type="file" wire:model="file" class="input-field !py-2">
                <button wire:click="upload" wire:loading.attr="disabled" class="btn-primary">{{ __('app.document_upload') }}</button>
            </div>
        @endif
    @endif

    @forelse($documents as $doc)
        <a href="{{ $doc->url }}" target="_blank" rel="noopener" class="menu-item block">
            <span class="icon-wrap"><x-ui.icon name="document" class="h-5 w-5" /></span>
            <span class="min-w-0 flex-1">
                <p class="truncate font-semibold">{{ $doc->title }}</p>
                <p class="text-xs" style="color:var(--muted)">{{ __('app.document_category_'.$doc->category) }} · {{ $doc->created_at->format('d M Y') }}</p>
            </span>
            <x-ui.icon name="arrow-right" class="h-5 w-5 opacity-40" />
        </a>
    @empty
        <div class="glass-card py-12 text-center">
            <x-ui.icon name="document" class="mx-auto h-12 w-12 opacity-30" style="color:var(--primary)" />
            <p class="mt-3 font-semibold" style="color:var(--muted)">{{ __('app.document_empty') }}</p>
        </div>
    @endforelse

    <div class="py-2">{{ $documents->links() }}</div>
</div>
