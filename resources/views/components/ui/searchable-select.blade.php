@props([
    'options' => [],
    'placeholder' => null,
    'searchPlaceholder' => null,
    'label' => null,
    'searchable' => true,
])

@php
    $wireModel = $attributes->wire('model');
    $wireKey = $wireModel->value();
    $placeholder ??= __('app.select_option');
    $searchPlaceholder ??= __('app.search');
    $optionsList = collect($options)->map(fn ($o) => [
        'value' => (string) ($o['value'] ?? ''),
        'label' => (string) ($o['label'] ?? ''),
    ])->values()->all();
@endphp

<div {{ $attributes->except(['wire:model', 'wire:model.live', 'wire:model.blur', 'wire:model.defer'])->class(['searchable-select-wrap']) }}>
    @if($label)
        <label class="mb-2 block text-sm font-semibold" style="color:var(--muted)">{{ $label }}</label>
    @endif

    <div
        class="searchable-select"
        x-data="{
            open: false,
            search: '',
            options: @js($optionsList),
            placeholder: @js($placeholder),
            searchPlaceholder: @js($searchPlaceholder),
            searchable: @js($searchable),
            selected: @entangle($wireKey).live,
            get selectedLabel() {
                if (this.selected === null || this.selected === '') return '';
                const opt = this.options.find(o => String(o.value) === String(this.selected));
                return opt ? opt.label : '';
            },
            get filtered() {
                if (!this.searchable || !this.search.trim()) return this.options;
                const q = this.search.toLowerCase();
                return this.options.filter(o =>
                    o.label.toLowerCase().includes(q) || String(o.value).toLowerCase().includes(q)
                );
            },
            pick(value) {
                this.selected = value;
                this.open = false;
                this.search = '';
            },
            toggle() {
                this.open = !this.open;
                if (this.open) {
                    this.$nextTick(() => this.$refs.searchInput?.focus());
                }
            }
        }"
        @click.outside="open = false"
    >
        <button
            type="button"
            class="searchable-select-trigger"
            @click="toggle()"
            :aria-expanded="open"
        >
            <span class="searchable-select-value" :class="{ 'is-placeholder': !selectedLabel }" x-text="selectedLabel || placeholder"></span>
            <span class="searchable-select-chevron" :class="{ 'is-open': open }">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5"/>
                </svg>
            </span>
        </button>

        <div x-show="open" x-transition.opacity.duration.150ms class="searchable-select-panel" x-cloak>
            <div x-show="searchable" class="searchable-select-search-wrap">
                <span class="searchable-select-search-icon">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-4.35-4.35M11 18a7 7 0 100-14 7 7 0 000 14z"/>
                    </svg>
                </span>
                <input
                    type="search"
                    x-ref="searchInput"
                    x-model="search"
                    class="searchable-select-search"
                    :placeholder="searchPlaceholder"
                    @click.stop
                    @keydown.escape="open = false"
                >
            </div>
            <ul class="searchable-select-list" role="listbox">
                <template x-for="opt in filtered" :key="opt.value + opt.label">
                    <li>
                        <button
                            type="button"
                            class="searchable-select-option"
                            :class="{ 'is-selected': String(selected) === String(opt.value) }"
                            @click="pick(opt.value)"
                            x-text="opt.label"
                        ></button>
                    </li>
                </template>
                <li x-show="filtered.length === 0" class="searchable-select-empty" x-text="@js(__('app.no_results'))"></li>
            </ul>
        </div>
    </div>
</div>
