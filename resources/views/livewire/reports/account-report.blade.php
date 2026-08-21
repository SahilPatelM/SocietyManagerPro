<div class="space-y-4">
    @if($report)
        <div class="hero-card p-4">
            <p class="text-sm text-white/80">{{ __('app.account_report') }}</p>
            <p class="text-xl font-bold">{{ __('app.house') }} {{ $report['house']->house_number }}</p>
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div class="stat-mini income text-center">
                <p class="text-xl font-bold text-emerald-600">₹{{ number_format($report['summary']['total_paid']) }}</p>
                <p class="text-xs font-semibold" style="color:var(--muted)">{{ __('app.total_paid') }}</p>
            </div>
            <div class="stat-mini warning text-center">
                <p class="text-xl font-bold text-amber-600">₹{{ number_format($report['summary']['total_pending']) }}</p>
                <p class="text-xs font-semibold" style="color:var(--muted)">{{ __('app.pending') }}</p>
            </div>
        </div>

        @foreach($report['bills'] as $bill)
            <div class="glass-card p-4">
                <div class="flex justify-between">
                    <p class="font-bold">{{ \Carbon\Carbon::createFromFormat('Y-m', $bill->month_year)->format('F Y') }}</p>
                    <span class="chip {{ $bill->status === 'paid' ? 'chip-success' : 'chip-warning' }}">{{ ucfirst($bill->status) }}</span>
                </div>
                <p class="mt-2 text-sm">₹{{ number_format($bill->paid_amount) }} / ₹{{ number_format($bill->totalDue()) }} {{ __('app.paid') }}</p>
            </div>
        @endforeach

        <a href="{{ route('maintenance.index') }}" class="btn-secondary block text-center">{{ __('app.maintenance') }}</a>
    @else
        <div class="glass-card py-12 text-center">
            <p style="color:var(--muted)">{{ __('app.no_house_linked') }}</p>
            @if(auth()->user()->canManageMaintenance())
                <a href="{{ route('maintenance.index') }}" class="btn-primary mt-4 inline-block">{{ __('app.maintenance') }}</a>
            @endif
        </div>
    @endif
</div>
