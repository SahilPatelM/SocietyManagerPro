<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = session('locale', auth()->user()?->locale ?? 'en');
        app()->setLocale(in_array($locale, ['en', 'gu']) ? $locale : 'en');

        return $next($request);
    }
}
