<?php

namespace App\Services\Tenancy;

use App\Models\Tenant;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class TenantDatabaseManager
{
    public function provision(Tenant $tenant): void
    {
        if ($tenant->database_mode !== 'separate' || empty($tenant->database_name)) {
            return;
        }

        $connection = config('database.default');
        $driver = config("database.connections.{$connection}.driver");

        if (! in_array($driver, ['pgsql', 'mysql', 'mariadb'], true)) {
            throw new RuntimeException("Tenant database provisioning is not supported for [{$driver}] connections.");
        }

        $databaseName = $this->validatedDatabaseName($tenant->database_name);

        match ($driver) {
            'pgsql' => $this->createPostgresDatabase($databaseName),
            'mysql', 'mariadb' => $this->createMySqlDatabase($databaseName),
        };

        $this->configureTenantConnection($connection, $databaseName);
        $this->runTenantMigrations();
    }

    private function createPostgresDatabase(string $databaseName): void
    {
        $exists = DB::selectOne('select 1 from pg_database where datname = ?', [$databaseName]);

        if ($exists) {
            return;
        }

        DB::statement(sprintf('create database "%s"', str_replace('"', '""', $databaseName)));
    }

    private function createMySqlDatabase(string $databaseName): void
    {
        DB::statement(sprintf(
            'create database if not exists `%s` character set utf8mb4 collate utf8mb4_unicode_ci',
            str_replace('`', '``', $databaseName)
        ));
    }

    private function configureTenantConnection(string $sourceConnection, string $databaseName): void
    {
        $tenantConnection = config("database.connections.{$sourceConnection}");
        $tenantConnection['database'] = $databaseName;

        Config::set('database.connections.tenant', $tenantConnection);
        DB::purge('tenant');
    }

    private function runTenantMigrations(): void
    {
        Artisan::call('migrate', [
            '--database' => 'tenant',
            '--path' => 'database/migrations/tenant',
            '--force' => true,
        ]);
    }

    private function validatedDatabaseName(string $databaseName): string
    {
        if (! preg_match('/^[a-z0-9_]+$/', $databaseName)) {
            throw new RuntimeException('Tenant database names may only contain lowercase letters, numbers, and underscores.');
        }

        return $databaseName;
    }
}
