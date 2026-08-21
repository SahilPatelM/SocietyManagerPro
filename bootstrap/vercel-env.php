<?php

/**
 * Vercel serverless bootstrap — runs before Laravel loads.
 */
if (! getenv('VERCEL') && ! getenv('VERCEL_URL')) {
    return;
}

putenv('VERCEL=1');
$_ENV['VERCEL'] = '1';
$_SERVER['VERCEL'] = '1';

// Drop stale provider/config caches (often contain dev-only packages like nativephp/mobile).
$cacheDir = dirname(__DIR__).'/bootstrap/cache';
foreach (['services.php', 'packages.php', 'config.php', 'routes.php', 'events.php'] as $file) {
    $path = $cacheDir.'/'.$file;
    if (is_file($path)) {
        @unlink($path);
    }
}

foreach (glob('/tmp/*.php') ?: [] as $cacheFile) {
    @unlink($cacheFile);
}

$host = getenv('VERCEL_URL')
    ?: ($_SERVER['HTTP_X_FORWARDED_HOST'] ?? $_SERVER['HTTP_HOST'] ?? '');

$host = preg_replace('#^https?://#i', '', (string) $host);
$host = trim(explode(',', $host)[0]);

if ($host !== '') {
    $httpsUrl = 'https://'.$host;

    putenv('APP_URL='.$httpsUrl);
    $_ENV['APP_URL'] = $httpsUrl;
    $_SERVER['APP_URL'] = $httpsUrl;

    putenv('ASSET_URL='.$httpsUrl);
    $_ENV['ASSET_URL'] = $httpsUrl;
    $_SERVER['ASSET_URL'] = $httpsUrl;
}

// Override dashboard env vars that break serverless.
$overrides = [
    'SESSION_DRIVER' => 'cookie',
    'CACHE_STORE' => 'array',
    'QUEUE_CONNECTION' => 'sync',
    'LOG_CHANNEL' => 'stderr',
];

foreach ($overrides as $key => $value) {
    putenv($key.'='.$value);
    $_ENV[$key] = $value;
    $_SERVER[$key] = $value;
}
