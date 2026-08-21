<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="{{ session('dark_mode') ? 'dark' : '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#6366f1">
    <x-pwa-meta />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? __('app.app_name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="min-h-screen pb-28 antialiased">
    <div class="app-bg" aria-hidden="true"></div>

    @unless(request()->routeIs('login'))
    <header class="app-header animate-fade-in">
        <div class="header-inner">
            <div class="min-w-0 flex-1">
                <p class="text-xs font-semibold uppercase tracking-wider" style="color:var(--muted)">{{ __('app.app_name') }}</p>
                <h1 class="page-title truncate">{{ $title ?? __('app.dashboard') }}</h1>
            </div>
            <div class="flex shrink-0 items-center gap-2">
                <button type="button" onclick="toggleDark()" class="btn-ghost" aria-label="{{ __('app.dark_mode') }}">
                    <x-ui.icon name="moon" class="h-5 w-5 dark:hidden" />
                    <x-ui.icon name="sun" class="hidden h-5 w-5 dark:block" />
                </button>
                <a href="{{ route('locale.switch', app()->getLocale() === 'en' ? 'gu' : 'en') }}" class="btn-ghost min-w-[44px] text-sm font-bold">
                    {{ app()->getLocale() === 'en' ? 'ગુ' : 'EN' }}
                </a>
                @auth
                <div class="avatar h-10 w-10 text-sm">
                    {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                </div>
                @endauth
            </div>
        </div>
    </header>
    @endunless

    <main class="relative px-4 py-4 {{ request()->routeIs('login') ? 'flex min-h-screen items-center' : '' }}">
        {{ $slot ?? '' }}
        @yield('content')
    </main>

    @auth
    <nav class="bottom-nav animate-fade-in-up stagger-3" aria-label="Main navigation">
        <a href="{{ route('dashboard') }}" wire:navigate class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <x-ui.icon name="home" />
            <span>{{ __('app.dashboard') }}</span>
        </a>
        <a href="{{ route('members.index') }}" wire:navigate class="nav-item {{ request()->routeIs('members.*') ? 'active' : '' }}">
            <x-ui.icon name="users" />
            <span>{{ __('app.members') }}</span>
        </a>
        <a href="{{ route('finance.index') }}" wire:navigate class="nav-item {{ request()->routeIs('finance.*') ? 'active' : '' }}">
            <x-ui.icon name="wallet" />
            <span>{{ __('app.finance') }}</span>
        </a>
        <a href="{{ route('complaints.index') }}" class="nav-item {{ request()->routeIs('complaints.*') ? 'active' : '' }}">
            <x-ui.icon name="clipboard" />
            <span>{{ __('app.complaints') }}</span>
        </a>
        <a href="{{ route('more') }}" wire:navigate class="nav-item {{ request()->routeIs('more') ? 'active' : '' }}">
            <x-ui.icon name="menu" />
            <span>{{ __('app.settings') }}</span>
        </a>
    </nav>
    @endauth

    <x-ui.toast-stack />
    <x-pwa-install-prompt />

    @livewireScripts
</body>
</html>
