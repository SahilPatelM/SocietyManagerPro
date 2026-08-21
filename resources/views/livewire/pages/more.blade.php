@extends('layouts.mobile')

@section('content')
<div class="space-y-3 animate-fade-in-up">
    <div class="hero-card mb-2 p-5">
        <div class="relative z-10 flex items-center gap-4">
            <div class="avatar h-16 w-16 text-2xl">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div>
                <p class="text-lg font-bold text-white">{{ auth()->user()->name }}</p>
                <p class="text-sm text-white/80">{{ auth()->user()->mobile }}</p>
                <span class="chip mt-2 bg-white/20 text-white capitalize">{{ auth()->user()->getRoleNames()->first() ?? 'member' }}</span>
            </div>
        </div>
    </div>

    <p class="px-1 pt-2 text-xs font-bold uppercase tracking-wider" style="color:var(--muted)">Modules</p>

    @php
    $menus = [
        ['route' => 'complaints.index', 'icon' => 'clipboard', 'label' => __('app.complaints')],
        ['route' => 'houses.index', 'icon' => 'building', 'label' => __('app.houses')],
        ['route' => 'reports.index', 'icon' => 'chart', 'label' => __('app.reports')],
        ['route' => 'announcements.index', 'icon' => 'megaphone', 'label' => __('app.announcements')],
        ['route' => 'polls.index', 'icon' => 'poll', 'label' => __('app.polls')],
        ['route' => 'visitors.index', 'icon' => 'user-plus', 'label' => __('app.visitors')],
        ['route' => 'documents.index', 'icon' => 'document', 'label' => __('app.documents')],
        ['route' => 'maintenance.index', 'icon' => 'receipt', 'label' => __('app.maintenance')],
        ['route' => 'parking.index', 'icon' => 'parking', 'label' => __('app.parking')],
    ];
    @endphp

    @foreach($menus as $i => $item)
        <a href="{{ route($item['route']) }}" class="menu-item animate-fade-in-up" style="animation-delay: {{ $i * 0.05 }}s">
            <span class="icon-wrap"><x-ui.icon :name="$item['icon']" class="h-5 w-5" /></span>
            <span class="flex-1 font-semibold">{{ $item['label'] }}</span>
            <x-ui.icon name="arrow-right" class="h-5 w-5 opacity-40" />
        </a>
    @endforeach

    <button
        type="button"
        class="menu-item w-full animate-fade-in-up text-left"
        style="animation-delay: 0.45s"
        onclick="window.dispatchEvent(new CustomEvent('pwa-install-request'))"
    >
        <span class="icon-wrap"><x-ui.icon name="home" class="h-5 w-5" /></span>
        <span class="flex-1 font-semibold">{{ __('app.pwa_install_app') }}</span>
        <x-ui.icon name="arrow-right" class="h-5 w-5 opacity-40" />
    </button>

    <form method="POST" action="{{ route('logout') }}" class="pt-4 animate-fade-in-up stagger-6">
        @csrf
        <button type="submit" class="btn-primary flex items-center justify-center gap-2">
            <x-ui.icon name="logout" class="h-5 w-5" />
            {{ __('app.logout') }}
        </button>
    </form>
</div>
@endsection
