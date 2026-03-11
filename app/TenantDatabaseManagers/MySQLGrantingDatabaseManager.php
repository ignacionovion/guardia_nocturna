<?php

declare(strict_types=1);

namespace App\TenantDatabaseManagers;

use Stancl\Tenancy\Contracts\TenantDatabaseManager;
use Stancl\Tenancy\Contracts\TenantWithDatabase;
use Illuminate\Support\Facades\DB;

/**
 * MySQL Database Manager that creates the database AND grants
 * full permissions to the Laravel DB user on the new database.
 * 
 * This solves the "SELECT command denied" error when the user
 * can CREATE DATABASE but doesn't have permissions on the new DB.
 */
class MySQLGrantingDatabaseManager implements TenantDatabaseManager
{
    protected string $connection;

    public function __construct()
    {
        $this->connection = config('tenancy.database.central_connection') ?? 'central';
    }

    public function createDatabase(TenantWithDatabase $tenant): bool
    {
        $database = $tenant->database()->getName();
        $charset = config("database.connections.{$this->connection}.charset", 'utf8mb4');
        $collation = config("database.connections.{$this->connection}.collation", 'utf8mb4_unicode_ci');

        // 1. Create the database
        DB::connection($this->connection)->statement(
            "CREATE DATABASE IF NOT EXISTS `{$database}` CHARACTER SET {$charset} COLLATE {$collation}"
        );

        // 2. Grant all privileges to the Laravel user on this new database
        $username = config("database.connections.{$this->connection}.username");
        $host = $this->getGrantHost();

        DB::connection($this->connection)->statement(
            "GRANT ALL PRIVILEGES ON `{$database}`.* TO '{$username}'@'{$host}'"
        );

        DB::connection($this->connection)->statement("FLUSH PRIVILEGES");

        return true;
    }

    public function deleteDatabase(TenantWithDatabase $tenant): bool
    {
        $database = $tenant->database()->getName();

        DB::connection($this->connection)->statement("DROP DATABASE IF EXISTS `{$database}`");

        return true;
    }

    public function databaseExists(string $name): bool
    {
        $result = DB::connection($this->connection)->select(
            "SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?",
            [$name]
        );

        return count($result) > 0;
    }

    public function makeConnectionConfig(array $baseConfig, string $databaseName): array
    {
        $baseConfig['database'] = $databaseName;

        return $baseConfig;
    }

    public function setConnection(string $connection): void
    {
        $this->connection = $connection;
    }

    /**
     * Determine the host part for GRANT statement.
     * In shared hosting, this is usually 'localhost'.
     */
    protected function getGrantHost(): string
    {
        return env('DB_GRANT_HOST', 'localhost');
    }
}
