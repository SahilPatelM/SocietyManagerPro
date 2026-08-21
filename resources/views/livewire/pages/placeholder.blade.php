@extends('layouts.mobile')

@section('content')
<div class="flex min-h-[60vh] flex-col items-center justify-center animate-scale-in">
    <div class="glass-card w-full max-w-sm p-10 text-center">
        <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-3xl" style="background:var(--gradient)">
            <x-ui.icon name="sparkles" class="h-10 w-10 text-white" style="animation: float 4s ease-in-out infinite" />
        </div>
        <h2 class="text-2xl font-extrabold">{{ $title }}</h2>
        <p class="mt-3 text-base font-medium" style="color:var(--muted)">Coming soon — API & database are ready.</p>
        <a href="{{ route('dashboard') }}" class="btn-primary mt-8 inline-flex items-center justify-center gap-2">
            <x-ui.icon name="home" class="h-5 w-5" />
            {{ __('app.dashboard') }}
        </a>
    </div>
</div>
@endsection
