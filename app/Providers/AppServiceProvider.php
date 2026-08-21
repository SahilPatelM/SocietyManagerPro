<?php

namespace App\Providers;

use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->configureForVercel();
        $this->applyDatabaseConfigFromEnvFile();
    }

    public function boot(): void
    {
        //
    }

    /**
     * Vercel serverless has a read-only filesystem except /tmp.
     */
    protected function configureForVercel(): void
    {
        if (! env('VERCEL')) {
            return;
        }

        $tmp = '/tmp';
        $storage = $tmp.'/laravel-storage';

        foreach ([
            $tmp,
            $tmp.'/views',
            $storage,
            $storage.'/framework/views',
            $storage.'/framework/cache',
            $storage.'/framework/sessions',
            $storage.'/logs',
        ] as $dir) {
            if (! is_dir($dir)) {
                @mkdir($dir, 0755, true);
            }
        }

        $this->app->useStoragePath($storage);

        config([
            'view.compiled' => env('VIEW_COMPILED_PATH', $tmp.'/views'),
            'session.driver' => env('SESSION_DRIVER', 'cookie'),
            'session.files' => $storage.'/framework/sessions',
            'cache.default' => env('CACHE_STORE', 'array'),
            'logging.default' => env('LOG_CHANNEL', 'stderr'),
            'logging.channels.stack.channels' => ['stderr'],
            'queue.default' => env('QUEUE_CONNECTION', 'sync'),
        ]);

        $appUrl = env('APP_URL');

        if ($vercelUrl = env('VERCEL_URL')) {
            $appUrl = 'https://'.$vercelUrl;
        } elseif ($appUrl) {
            $appUrl = preg_replace('#^http://#i', 'https://', $appUrl);
        }

        if ($appUrl) {
            config(['app.url' => $appUrl, 'app.asset_url' => $appUrl]);
            URL::forceRootUrl(rtrim($appUrl, '/'));
        }

        URL::forceScheme('https');
    }

    /**
     * Windows/shell often sets DB_CONNECTION=sqlite, which overrides .env.
     * Read database settings from .env (Supabase / PostgreSQL).
     */
    protected function applyDatabaseConfigFromEnvFile(): void
    {
        $path = base_path('.env');

        if (! is_readable($path)) {
            return;
        }

        $connection = null;

        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
                continue;
            }

            [$name, $value] = array_map('trim', explode('=', $line, 2));
            $value = trim($value, "\"'");

            if ($name === 'DB_CONNECTION') {
                $connection = $value;
                config(['database.default' => $value]);
            } elseif ($name === 'DB_URL') {
                config(['database.connections.pgsql.url' => $value]);
            } elseif ($connection && str_starts_with($name, 'DB_')) {
                $key = match ($name) {
                    'DB_HOST' => 'host',
                    'DB_PORT' => 'port',
                    'DB_DATABASE' => 'database',
                    'DB_USERNAME' => 'username',
                    'DB_PASSWORD' => 'password',
                    'DB_SSLMODE' => 'sslmode',
                    default => null,
                };

                if ($key !== null) {
                    config(["database.connections.{$connection}.{$key}" => $value]);
                }
            }
        }
    }
}
