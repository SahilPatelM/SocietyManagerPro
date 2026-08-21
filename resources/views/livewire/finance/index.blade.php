<div class="space-y-4">
    @if($isAdmin)
        <button type="button" wire:click="$toggle('showForm')" class="btn-primary !min-h-[48px]">
            {{ $showForm ? '− ' : '+ ' }}{{ __('app.finance_add') }}
        </button>

        @if($showForm)
            <div class="glass-card space-y-3 p-4">
                <div class="segmented">
                    <button type="button" wire:click="$set('formType', 'income'); $set('category', 'maintenance')" class="{{ $formType === 'income' ? 'active' : '' }}">{{ __('app.income') }}</button>
                    <button type="button" wire:click="$set('formType', 'expense'); $set('category', 'electricity')" class="{{ $formType === 'expense' ? 'active' : '' }}">{{ __('app.expense') }}</button>
                </div>
                <x-ui.searchable-select
                    wire:model="category"
                    wire:key="finance-category-{{ $formType }}"
                    :options="collect($formType === 'income' ? $incomeCategories : $expenseCategories)->map(fn ($cat) => ['value' => $cat, 'label' => ucfirst(str_replace('_', ' ', $cat))])->values()->all()"
                />
                <input type="number" wire:model="amount" class="input-field" step="0.01" placeholder="{{ __('app.amount') }}">
                <input type="date" wire:model="transactionDate" class="input-field">
                <input wire:model="description" class="input-field" placeholder="{{ __('app.finance_description') }}">
                <button wire:click="save" wire:loading.attr="disabled" class="btn-primary">{{ __('app.save') }}</button>
            </div>
        @endif
    @endif

    <div class="segmented animate-fade-in-up" data-animate>
        <button type="button" wire:click="$set('type', null)" class="{{ !$type ? 'active' : '' }}">All</button>
        <button type="button" wire:click="$set('type', 'income')" class="{{ $type === 'income' ? 'active' : '' }}">{{ __('app.income') }}</button>
        <button type="button" wire:click="$set('type', 'expense')" class="{{ $type === 'expense' ? 'active' : '' }}">{{ __('app.expense') }}</button>
    </div>

    <div wire:loading wire:target="type" class="space-y-3">
        @for($i = 0; $i < 3; $i++)
            <div class="skeleton h-24 w-full"></div>
        @endfor
    </div>

    <div wire:loading.remove wire:target="type" class="space-y-3">
        @forelse($transactions as $index => $tx)
            <div class="glass-card animate-fade-in-up p-4 {{ $tx->type === 'income' ? 'income' : 'expense' }}" data-animate style="animation-delay: {{ min($index * 0.04, 0.24) }}s">
                <div class="flex items-center gap-4">
                    <div class="flex h-12 w-12 shrink-0 items-center justify-center rounded-2xl {{ $tx->type === 'income' ? 'bg-emerald-500/15 text-emerald-600' : 'bg-rose-500/15 text-rose-600' }}">
                        @if($tx->type === 'income')
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                        @else
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/></svg>
                        @endif
                    </div>
                    <div class="min-w-0 flex-1">
                        <p class="truncate font-bold capitalize">{{ str_replace('_', ' ', $tx->category) }}</p>
                        <p class="text-sm font-medium" style="color:var(--muted)">{{ $tx->transaction_date->format('d M Y') }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xl font-extrabold {{ $tx->type === 'income' ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $tx->type === 'income' ? '+' : '−' }}₹{{ number_format($tx->amount) }}
                        </p>
                        <span class="chip {{ $tx->type === 'income' ? 'chip-income' : 'chip-expense' }} mt-1">
                            {{ $tx->type }}
                        </span>
                    </div>
                </div>
            </div>
        @empty
            <div class="glass-card py-16 text-center animate-scale-in">
                <x-ui.icon name="wallet" class="mx-auto h-14 w-14 opacity-30" style="color:var(--primary)" />
                <p class="mt-4 text-lg font-semibold">No transactions yet</p>
            </div>
        @endforelse
    </div>

    <div class="py-2">{{ $transactions->links() }}</div>
</div>
