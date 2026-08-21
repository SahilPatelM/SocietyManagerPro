<div class="space-y-4">
    @if($isAdmin)
        <div class="segmented">
            <button type="button" wire:click="$set('maintenanceType', 'general')" class="{{ $maintenanceType === 'general' ? 'active' : '' }}">{{ __('app.maintenance_type_general') }}</button>
            <button type="button" wire:click="$set('maintenanceType', 'parking')" class="{{ $maintenanceType === 'parking' ? 'active' : '' }}">{{ __('app.maintenance_type_parking') }}</button>
        </div>

        {{-- Admin / Treasurer --}}
        <div class="hero-card p-4">
            <p class="text-sm text-white/80">
                {{ $maintenanceType === 'parking' ? __('app.maintenance_type_parking') : __('app.current_month_maintenance') }}
            </p>
            <p class="text-2xl font-extrabold">{{ \Carbon\Carbon::createFromFormat('Y-m', $monthYear)->format('F Y') }}</p>
            @if($cycle?->bills_generated)
                <span class="chip mt-2 bg-white/20 text-white">{{ __('app.bills_generated') }}</span>
            @endif
        </div>

        <button wire:click="$toggle('showGenerate')" class="btn-primary !min-h-[48px]">
            {{ $showGenerate ? '− ' : '+ ' }}{{ __('app.generate_maintenance') }}
        </button>

        @if($showGenerate)
            <div class="glass-card space-y-3 p-4 animate-scale-in">
                <p class="text-sm font-semibold" style="color:var(--muted)">
                    {{ $maintenanceType === 'parking' ? __('app.maintenance_parking_hint') : __('app.maintenance_general_hint') }}
                </p>
                <div>
                    <label class="login-label">{{ __('app.month') }}</label>
                    <input type="month" wire:model.live="monthYear" class="input-field">
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="login-label">{{ __('app.amount') }}</label>
                        <input type="number" wire:model="amount" class="input-field" step="0.01">
                    </div>
                    <div>
                        <label class="login-label">{{ __('app.late_fee') }}</label>
                        <input type="number" wire:model="lateFee" class="input-field" step="0.01">
                    </div>
                </div>
                <div>
                    <label class="login-label">{{ __('app.due_date') }}</label>
                    <input type="date" wire:model="dueDate" class="input-field">
                </div>
                <label class="flex items-center gap-2 text-sm font-semibold">
                    <input type="checkbox" wire:model="sendNotifications" class="h-5 w-5 rounded">
                    {{ __('app.notify_all_members') }}
                </label>
                <button wire:click="generateBills" wire:loading.attr="disabled" class="btn-primary">
                    <span wire:loading.remove wire:target="generateBills">{{ __('app.generate_and_notify') }}</span>
                    <span wire:loading wire:target="generateBills">{{ __('app.processing') }}…</span>
                </button>
            </div>
        @endif

        <div class="search-box">
            <span class="search-icon"><x-ui.icon name="search" class="h-5 w-5" /></span>
            <input
                type="search"
                wire:model.live.debounce.300ms="search"
                class="input-field"
                placeholder="{{ __('app.search_house_member') }}"
            >
        </div>

        <div class="segmented">
            <button type="button" wire:click="$set('filterStatus', '')" class="{{ $filterStatus === '' ? 'active' : '' }}">{{ __('app.all') }}</button>
            <button type="button" wire:click="$set('filterStatus', 'pending')" class="{{ $filterStatus === 'pending' ? 'active' : '' }}">{{ __('app.pending') }}</button>
            <button type="button" wire:click="$set('filterStatus', 'paid')" class="{{ $filterStatus === 'paid' ? 'active' : '' }}">{{ __('app.paid') }}</button>
        </div>

        <div wire:loading wire:target="search" class="skeleton h-16 w-full rounded-2xl"></div>

        @forelse($bills as $bill)
            <div class="glass-card p-4">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <p class="font-bold">{{ $bill->house?->house_number }} · {{ $bill->house?->owner?->name ?? '—' }}</p>
                        <p class="text-sm font-medium" style="color:var(--muted)">{{ $bill->bill_number }}</p>
                        @if(($bill->bill_type ?? 'general') === 'parking')
                            <span class="chip chip-neutral mt-1 text-xs">{{ __('app.maintenance_type_parking') }}</span>
                        @endif
                    </div>
                    @php
                        $chip = match($bill->status) {
                            'paid' => 'chip-success',
                            'partial' => 'chip-info',
                            'overdue' => 'chip-expense',
                            default => 'chip-warning',
                        };
                    @endphp
                    <span class="chip {{ $chip }}">{{ ucfirst($bill->status) }}</span>
                </div>
                <div class="mt-3 flex justify-between text-sm">
                    <span style="color:var(--muted)">{{ __('app.due') }}: ₹{{ number_format($bill->totalDue()) }}</span>
                    <span class="font-bold text-emerald-600">{{ __('app.paid') }}: ₹{{ number_format($bill->paid_amount) }}</span>
                </div>
                @if($bill->balanceDue() > 0 && auth()->user()->canManageMaintenance())
                    <button wire:click="openPayModal({{ $bill->id }})" class="btn-primary mt-3 !min-h-[44px] !text-base">
                        {{ __('app.mark_as_paid') }}
                    </button>
                @endif
            </div>
        @empty
            <div class="glass-card py-10 text-center">
                <x-ui.icon name="search" class="mx-auto h-10 w-10 opacity-25" style="color:var(--primary)" />
                <p class="mt-3 font-semibold" style="color:var(--muted)">
                    {{ $search ? __('app.no_search_results') : __('app.no_bills_month') }}
                </p>
            </div>
        @endforelse

        <div>{{ $bills->links() }}</div>

    @else
        {{-- Member account report --}}
        @if($accountReport)
            <div class="hero-card p-4">
                <p class="text-sm text-white/80">{{ __('app.house') }} {{ $accountReport['house']->house_number }}</p>
                <p class="text-lg font-bold">{{ $accountReport['house']->owner?->name }}</p>
            </div>

            <div class="grid grid-cols-3 gap-2">
                <div class="stat-mini text-center">
                    <p class="text-lg font-bold text-emerald-600">₹{{ number_format($accountReport['summary']['total_paid']) }}</p>
                    <p class="text-xs font-semibold" style="color:var(--muted)">{{ __('app.total_paid') }}</p>
                </div>
                <div class="stat-mini warning text-center">
                    <p class="text-lg font-bold text-amber-600">₹{{ number_format($accountReport['summary']['total_pending']) }}</p>
                    <p class="text-xs font-semibold" style="color:var(--muted)">{{ __('app.pending') }}</p>
                </div>
                <div class="stat-mini text-center">
                    <p class="text-lg font-bold">{{ $accountReport['summary']['months_paid'] }}/{{ $accountReport['bills']->count() }}</p>
                    <p class="text-xs font-semibold" style="color:var(--muted)">{{ __('app.months_paid') }}</p>
                </div>
            </div>

            @if(! $accountReport['has_active_parking'])
                <p class="rounded-2xl border px-4 py-3 text-sm font-medium" style="border-color:var(--border);color:var(--muted)">
                    {{ __('app.maintenance_parking_member_hint') }}
                </p>
            @endif

            <h3 class="text-sm font-bold uppercase tracking-wider" style="color:var(--muted)">{{ __('app.account_report') }}</h3>

            @foreach($accountReport['bills'] as $bill)
                <div class="list-item flex-col !items-stretch gap-2">
                    <div class="flex w-full justify-between">
                        <p class="font-bold">
                            {{ \Carbon\Carbon::createFromFormat('Y-m', $bill->month_year)->format('F Y') }}
                            @if(($bill->bill_type ?? 'general') === 'parking')
                                <span class="chip chip-neutral ml-1 text-xs">{{ __('app.maintenance_type_parking') }}</span>
                            @endif
                        </p>
                        @php
                            $chip = match($bill->status) {
                                'paid' => 'chip-success',
                                'partial' => 'chip-info',
                                'overdue' => 'chip-expense',
                                default => 'chip-warning',
                            };
                        @endphp
                        <span class="chip {{ $chip }}">{{ ucfirst($bill->status) }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span>{{ __('app.amount') }}: ₹{{ number_format($bill->maintenance_amount) }}</span>
                        @if($bill->late_fee > 0)
                            <span class="text-amber-600">+{{ __('app.late_fee') }} ₹{{ number_format($bill->late_fee) }}</span>
                        @endif
                    </div>
                    <div class="flex justify-between font-semibold">
                        <span class="text-emerald-600">{{ __('app.paid') }}: ₹{{ number_format($bill->paid_amount) }}</span>
                        @if($bill->balanceDue() > 0)
                            <span class="text-rose-600">{{ __('app.pending') }}: ₹{{ number_format($bill->balanceDue()) }}</span>
                        @endif
                    </div>
                    @if($bill->payments->isNotEmpty())
                        <div class="mt-1 border-t pt-2" style="border-color:var(--border)">
                            @foreach($bill->payments as $payment)
                                <p class="text-xs" style="color:var(--muted)">
                                    {{ $payment->payment_date->format('d M Y') }} ·
                                    {{ strtoupper($payment->payment_method) }} ·
                                    ₹{{ number_format($payment->amount) }}
                                </p>
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        @else
            <div class="glass-card py-10 text-center">
                <p style="color:var(--muted)">{{ __('app.no_house_linked') }}</p>
            </div>
        @endif
    @endif

    @if($showPayModal)
        <div class="sheet-backdrop" wire:click="closePayModal" aria-hidden="true"></div>
        <div class="sheet-modal" role="dialog" aria-modal="true">
            <div class="sheet-handle" aria-hidden="true"></div>

            <div class="sheet-header">
                <div>
                    <h3 class="sheet-title">{{ __('app.mark_as_paid') }}</h3>
                    <p class="sheet-subtitle">{{ $payBillNumber }}</p>
                </div>
                <button type="button" wire:click="closePayModal" class="sheet-close" aria-label="{{ __('app.cancel') }}">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="sheet-summary">
                <div class="sheet-summary-item">
                    <span class="sheet-summary-label">{{ __('app.house') }}</span>
                    <span class="sheet-summary-value">{{ $payHouseNumber }}</span>
                </div>
                <div class="sheet-summary-item">
                    <span class="sheet-summary-label">{{ __('app.members') }}</span>
                    <span class="sheet-summary-value truncate">{{ $payOwnerName }}</span>
                </div>
                <div class="sheet-summary-item sheet-summary-highlight">
                    <span class="sheet-summary-label">{{ __('app.balance_due') }}</span>
                    <span class="sheet-summary-value text-amber-600">₹{{ number_format($payBalanceDue) }}</span>
                </div>
            </div>

            <div class="sheet-body">
                <div class="flex items-center justify-between">
                    <label class="login-label !mb-0">{{ __('app.amount') }}</label>
                    <button type="button" wire:click="payFullAmount" class="text-sm font-bold" style="color:var(--primary)">
                        {{ __('app.pay_full_amount') }}
                    </button>
                </div>
                <div class="login-input-wrap mt-2">
                    <span class="login-input-prefix">₹</span>
                    <input type="number" wire:model="payAmount" class="login-input" step="0.01" min="0.01" inputmode="decimal">
                </div>

                <p class="login-label mt-4">{{ __('app.payment_method') }}</p>
                <div class="pay-method-grid">
                    <button
                        type="button"
                        wire:click="$set('paymentMethod', 'cash')"
                        class="pay-method-card {{ $paymentMethod === 'cash' ? 'active' : '' }}"
                    >
                        <span class="pay-method-icon">💵</span>
                        <span class="pay-method-label">{{ __('app.cash') }}</span>
                    </button>
                    <button
                        type="button"
                        wire:click="$set('paymentMethod', 'online')"
                        class="pay-method-card {{ $paymentMethod === 'online' ? 'active' : '' }}"
                    >
                        <span class="pay-method-icon">📱</span>
                        <span class="pay-method-label">{{ __('app.online') }}</span>
                    </button>
                </div>
            </div>

            <div class="sheet-footer">
                <button wire:click="markPaid" wire:loading.attr="disabled" class="btn-primary !min-h-[52px]">
                    <span wire:loading.remove wire:target="markPaid">{{ __('app.confirm_payment') }}</span>
                    <span wire:loading wire:target="markPaid">{{ __('app.processing') }}…</span>
                </button>
                <button type="button" wire:click="closePayModal" class="btn-secondary !min-h-[48px] !mt-0">
                    {{ __('app.cancel') }}
                </button>
            </div>
        </div>
    @endif
</div>
