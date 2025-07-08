<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Set up database configuration to use Docker secrets
        $this->configureDatabaseSecrets();

        // Set up mail configuration to use Docker secrets
        $this->configureMailSecrets();

        // Set up domain configuration to use Docker secrets with fallbacks
        $this->configureDomainSecrets();
    }

    /**
     * Configure database settings to use Docker secrets
     */
    private function configureDatabaseSecrets(): void
    {
        // Read database secrets from Docker secrets files
        $dbHost = $this->readDockerSecret('db_host');
        $dbPort = $this->readDockerSecret('db_port');
        $dbDatabase = $this->readDockerSecret('db_database');
        $dbUsername = $this->readDockerSecret('db_username');
        $dbPassword = $this->readDockerSecret('db_password');

        // Set database configuration if secrets are available
        if ($dbHost && $dbPort && $dbDatabase && $dbUsername && $dbPassword) {
            config([
                'database.connections.mysql.host' => $dbHost,
                'database.connections.mysql.port' => $dbPort,
                'database.connections.mysql.database' => $dbDatabase,
                'database.connections.mysql.username' => $dbUsername,
                'database.connections.mysql.password' => $dbPassword,
            ]);
        }
    }

    /**
     * Configure mail settings to use Docker secrets
     */
    private function configureMailSecrets(): void
    {
        // Read mail secrets from Docker secrets files
        $mailUsername = $this->readDockerSecret('mail_username');
        $mailPassword = $this->readDockerSecret('mail_password');

        // Set mail configuration if secrets are available
        if ($mailUsername && $mailPassword) {
            config([
                'mail.mailers.smtp.username' => $mailUsername,
                'mail.mailers.smtp.password' => $mailPassword,
            ]);
        }
    }

    /**
     * Configure domain settings to use Docker secrets with layered fallback
     * Three-layer resolution: secrets → environment → defaults
     */
    private function configureDomainSecrets(): void
    {
        // Set APP_URL from secrets with fallback to environment then defaults
        $appUrl = $this->readDockerSecret('app_url')
            ?? env('APP_URL')
            ?? 'http://localhost:7010';
        config(['app.url' => $appUrl]);

        // Configure CORS origins with layered fallback
        $corsOrigins = $this->readDockerSecret('cors_origins')
            ?? env('CORS_ORIGINS')
            ?? 'http://localhost:5010,http://localhost:5173';
        config(['cors.allowed_origins' => explode(',', $corsOrigins)]);

        // Configure Sanctum stateful domains with layered fallback
        $sanctumDomains = $this->readDockerSecret('sanctum_domains')
            ?? env('SANCTUM_STATEFUL_DOMAINS')
            ?? 'localhost,localhost:5010,127.0.0.1:5010';
        config(['sanctum.stateful' => explode(',', $sanctumDomains)]);
    }

    /**
     * Read Docker secret from file
     */
    private function readDockerSecret(string $secretName): ?string
    {
        $secretPath = '/run/secrets/' . $secretName;

        if (file_exists($secretPath)) {
            return trim(file_get_contents($secretPath));
        }

        return null;
    }
}
