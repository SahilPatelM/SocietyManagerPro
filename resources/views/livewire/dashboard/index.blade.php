<div class="space-y-5">
    {{-- Hero balance --}}
    <div class="hero-card animate-fade-in-up p-6" data-animate>
        <div class="relative z-10">
            <div class="flex items-start justify-between">
                <div>
                    <p class="text-sm font-medium text-white/80">{{ __('app.balance') }}</p>
                    <p class="mt-1 text-4xl font-extrabold tracking-tight">₹{{ number_format($stats['current_balance'] ?? 0) }}</p>
                </div>
                <div class="flex h-12 w-12 items-center justify-center rounded-2xl bg-white/20 backdrop-blur">
                    <x-ui.icon name="sparkles" class="h-6 w-6 text-white" />
                </div>
            </div>
            <div class="mt-5 flex gap-4 border-t border-white/20 pt-4">
                <div>
                    <p class="text-xs text-white/70">{{ __('app.today_collection') }}</p>
                    <p class="text-lg font-bold">₹{{ number_format($stats['today_collection'] ?? 0) }}</p>
                </div>
                <div class="h-10 w-px bg-white/20"></div>
                <div>
                    <p class="text-xs text-white/70">This Month</p>
                    <p class="text-lg font-bold">₹{{ number_format($stats['current_month_collection'] ?? 0) }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Stat grid --}}
    <div class="grid grid-cols-2 gap-3">
        <div class="stat-mini income animate-fade-in-up stagger-1" data-animate>
            <div class="mb-2 flex h-9 w-9 items-center justify-center rounded-xl bg-emerald-500/15 text-emerald-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            </div>
            <p class="text-xs font-semibold uppercase tracking-wide" style="color:var(--muted)">{{ __('app.total_income') }}</p>
            <p class="text-xl font-bold text-emerald-600">₹{{ number_format($stats['total_income'] ?? 0) }}</p>
        </div>
        <div class="stat-mini expense animate-fade-in-up stagger-2" data-animate>
            <div class="mb-2 flex h-9 w-9 items-center justify-center rounded-xl bg-rose-500/15 text-rose-600">
                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/></svg>
            </div>
            <p class="text-xs font-semibold uppercase tracking-wide" style="color:var(--muted)">{{ __('app.total_expenses') }}</p>
            <p class="text-xl font-bold text-rose-600">₹{{ number_format($stats['total_expenses'] ?? 0) }}</p>
        </div>
        <div class="stat-mini warning animate-fade-in-up stagger-3" data-animate>
            <p class="text-xs font-semibold uppercase tracking-wide" style="color:var(--muted)">{{ __('app.pending_maintenance') }}</p>
            <p class="text-xl font-bold text-amber-600">₹{{ number_format($stats['pending_maintenance'] ?? 0) }}</p>
        </div>
        <div class="stat-mini animate-fade-in-up stagger-4" data-animate>
            <p class="text-xs font-semibold uppercase tracking-wide" style="color:var(--muted)">Pending Dues</p>
            <p class="text-xl font-bold" style="color:var(--primary)">₹{{ number_format($stats['pending_dues'] ?? 0) }}</p>
        </div>
    </div>

    {{-- Houses overview --}}
    <div class="glass-card animate-fade-in-up stagger-4 p-5" data-animate>
        <div class="mb-4 flex items-center justify-between">
            <h3 class="flex items-center gap-2 text-lg font-bold">
                <x-ui.icon name="building" class="h-5 w-5" style="color:var(--primary)" />
                {{ __('app.houses') }}
            </h3>
            <a href="{{ route('houses.index') }}" class="text-sm font-semibold" style="color:var(--primary)">View all</a>
        </div>
        <div class="grid grid-cols-3 gap-3">
            <div class="rounded-2xl p-3 text-center" style="background:rgba(99,102,241,0.08)">
                <p class="text-2xl font-extrabold">{{ $stats['total_houses'] ?? 0 }}</p>
                <p class="mt-1 text-xs font-semibold" style="color:var(--muted)">Total</p>
            </div>
            <div class="rounded-2xl p-3 text-center" style="background:rgba(16,185,129,0.1)">
                <p class="text-2xl font-extrabold text-emerald-600">{{ $stats['occupied_houses'] ?? 0 }}</p>
                <p class="mt-1 text-xs font-semibold" style="color:var(--muted)">Occupied</p>
            </div>
            <div class="rounded-2xl p-3 text-center" style="background:rgba(245,158,11,0.1)">
                <p class="text-2xl font-extrabold text-amber-600">{{ $stats['vacant_houses'] ?? 0 }}</p>
                <p class="mt-1 text-xs font-semibold" style="color:var(--muted)">Vacant</p>
            </div>
        </div>
    </div>

    {{-- Quick actions --}}
    <div class="animate-fade-in-up stagger-5" data-animate>
        <h3 class="mb-3 text-sm font-bold uppercase tracking-wider" style="color:var(--muted)">Quick Actions</h3>
        <div class="grid grid-cols-2 gap-3">
            <a href="{{ route('members.index') }}" class="action-tile">
                <span class="icon-wrap"><x-ui.icon name="users" class="h-6 w-6" /></span>
                <span>{{ __('app.members') }}</span>
            </a>
            <a href="{{ route('finance.index') }}" class="action-tile">
                <span class="icon-wrap"><x-ui.icon name="wallet" class="h-6 w-6" /></span>
                <span>{{ __('app.finance') }}</span>
            </a>
            <a href="{{ route('houses.index') }}" class="action-tile">
                <span class="icon-wrap"><x-ui.icon name="building" class="h-6 w-6" /></span>
                <span>{{ __('app.houses') }}</span>
            </a>
            <a href="{{ route('reports.index') }}" class="action-tile">
                <span class="icon-wrap"><x-ui.icon name="chart" class="h-6 w-6" /></span>
                <span>{{ __('app.reports') }}</span>
            </a>
        </div>
    </div>

    {{-- Footer stats --}}
    <div class="flex animate-fade-in-up stagger-6 justify-center gap-6 rounded-2xl py-4" style="background:var(--card);border:1px solid var(--border)" data-animate>
        <div class="text-center">
            <p class="text-2xl font-bold">{{ $stats['total_members'] ?? 0 }}</p>
            <p class="text-xs font-semibold" style="color:var(--muted)">Members</p>
        </div>
        <div class="w-px" style="background:var(--border)"></div>
        <a href="{{ route('complaints.index') }}" class="text-center">
            <p class="text-2xl font-bold text-amber-600">{{ $stats['complaint_count'] ?? 0 }}</p>
            <p class="text-xs font-semibold" style="color:var(--muted)">{{ __('app.complaints') }}</p>
        </a>
    </div>
</div>
