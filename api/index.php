<?php

/**
 * Vercel serverless entry point for Laravel.
 */
chdir(dirname(__DIR__));

require_once __DIR__.'/../bootstrap/vercel-env.php';

try {
    require __DIR__.'/../public/index.php';
} catch (Throwable $e) {
    error_log('[SocietyManagerPro] '.$e->getMessage().' @ '.$e->getFile().':'.$e->getLine());
    http_response_code(500);

    if (filter_var(getenv('APP_DEBUG'), FILTER_VALIDATE_BOOLEAN)) {
        header('Content-Type: text/plain; charset=UTF-8');
        echo 'Error: '.$e->getMessage();
    }
}
