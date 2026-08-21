<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="login-page {{ session('dark_mode') ? 'dark' : '' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="theme-color" content="#4f46e5">
    <x-pwa-meta />
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('app.login') }} · {{ __('app.app_name') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="login-body">
    {{ $slot }}

    <x-ui.toast-stack />
    <x-pwa-install-prompt />

    @livewireScripts
</body>
</html>
