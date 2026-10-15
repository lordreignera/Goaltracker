<?php

namespace App\Console\Commands;

use App\Services\AuditLogger;
use App\Services\BackupManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Throwable;

class TestBackupRestore extends Command
{
    protected $signature = 'backup:restore-test {archive? : Backup path on the configured disk}';

    protected $description = 'Restore a backup into the separately configured restore-test database';

    public function handle(BackupManager $backups, AuditLogger $audit): int
    {
        $archive = $this->argument('archive') ?: $backups->latestPath();
        $target = (string) config('backup.restore_test_database');

        if (! $archive) {
            $this->error('No backup archive is available to restore.');

            return self::FAILURE;
        }

        if ($target === '') {
            $this->error('Set BACKUP_RESTORE_TEST_DATABASE to a separate disposable database first.');

            return self::FAILURE;
        }

        try {
            $manifest = $backups->restore($archive, $target);
            $this->validateRestoredDatabase($target, $manifest['database_driver']);
            $audit->record('backup', 'backup.restore_test_passed', 'Backup restore test passed.', metadata: [
                'path' => $archive,
                'target' => basename($target),
            ]);
            $this->info("Restore test passed using [{$target}].");

            return self::SUCCESS;
        } catch (Throwable $exception) {
            report($exception);
            $audit->record('backup', 'backup.restore_test_failed', 'Backup restore test failed.', metadata: [
                'path' => $archive,
                'target' => basename($target),
                'error' => $exception->getMessage(),
            ]);
            $this->error('Restore test failed: '.$exception->getMessage());

            return self::FAILURE;
        }
    }

    private function validateRestoredDatabase(string $target, string $driver): void
    {
        $connectionName = 'backup_restore_test';
        $configuration = config('database.connections.'.config('database.default'));
        $configuration['database'] = $target;
        config(["database.connections.{$connectionName}" => $configuration]);

        try {
            $connection = DB::connection($connectionName);
            $tables = match ($driver) {
                'sqlite' => $connection->select("SELECT name FROM sqlite_master WHERE type = 'table' AND name = 'migrations'"),
                'mysql', 'mariadb' => $connection->select("SHOW TABLES LIKE 'migrations'"),
                'pgsql' => $connection->select("SELECT to_regclass('public.migrations') AS table_name"),
                default => [],
            };

            if ($tables === []) {
                throw new \RuntimeException('The restored database does not contain the migrations table.');
            }
        } finally {
            DB::purge($connectionName);
        }
    }
}
