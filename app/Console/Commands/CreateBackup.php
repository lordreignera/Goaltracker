<?php

namespace App\Console\Commands;

use App\Services\AuditLogger;
use App\Services\BackupManager;
use Illuminate\Console\Command;
use Throwable;

class CreateBackup extends Command
{
    protected $signature = 'backup:create';

    protected $description = 'Create and verify a database and evidence backup';

    public function handle(BackupManager $backups, AuditLogger $audit): int
    {
        if (! config('backup.enabled')) {
            $this->warn('Backups are disabled by configuration.');

            return self::SUCCESS;
        }

        try {
            $backup = $backups->create();
            $backups->verify($backup['path']);
            $audit->record('backup', 'backup.created', 'Created and verified a system backup.', metadata: [
                'path' => $backup['path'],
                'size' => $backup['size'],
                'evidence_files' => $backup['manifest']['evidence_files'],
            ]);
            $this->info("Backup created and verified: {$backup['path']}");

            return self::SUCCESS;
        } catch (Throwable $exception) {
            report($exception);
            $audit->record('backup', 'backup.failed', 'System backup failed.', metadata: [
                'error' => $exception->getMessage(),
            ]);
            $this->error('Backup failed: '.$exception->getMessage());

            return self::FAILURE;
        }
    }
}
