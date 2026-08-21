<?php

/**
 * Force HTTPS URLs on Vercel before Laravel loads config.
 * Fixes mixed-content blocking for CSS, JS, Livewire, and PWA assets.
 */
if (! getenv('VERCEL') && ! getenv('VERCEL_URL')) {
    return;
}

$host = getenv('VERCEL_URL') ?: ($_SERVER['HTTP_HOST'] ?? '');
$host = preg_replace('#^https?://#i', '', $host);
$httpsUrl = 'https://'.$host;

putenv('APP_URL='.$httpsUrl);
$_ENV['APP_URL'] = $httpsUrl;
$_SERVER['APP_URL'] = $httpsUrl;

putenv('ASSET_URL='.$httpsUrl);
$_ENV['ASSET_URL'] = $httpsUrl;
$_SERVER['ASSET_URL'] = $httpsUrl;

foreach ([
    '/tmp/config.php',
    '/tmp/routes.php',
    '/tmp/services.php',
    '/tmp/events.php',
    '/tmp/packages.php',
] as $cacheFile) {
    if (is_file($cacheFile)) {
        @unlink($cacheFile);
    }
}
