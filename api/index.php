<?php

/**
 * Vercel serverless entry point for Laravel.
 * @see https://github.com/vercel-community/php
 */
chdir(dirname(__DIR__));

// Force HTTPS app URL before Laravel boots (fixes mixed-content CSS/JS).
if ($vercelUrl = getenv('VERCEL_URL')) {
    $httpsUrl = 'https://'.$vercelUrl;

    putenv('APP_URL='.$httpsUrl);
    $_ENV['APP_URL'] = $httpsUrl;
    $_SERVER['APP_URL'] = $httpsUrl;
}

require __DIR__.'/../public/index.php';
