<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->applyDatabaseConfigFromEnvFile();
    }

    public function boot(): void
    {
        //
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
