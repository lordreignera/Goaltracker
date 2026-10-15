<?php

namespace App\Console\Commands;

use App\Services\AuditLogger;
use App\Services\BackupManager;
use Illuminate\Console\Command;
use Throwable;

class VerifyBackup extends Command
{
    protected $signature = 'backup:verify {archive? : Backup path on the configured disk}';

    protected $description = 'Verify the checksums of a backup archive';

    public function handle(BackupManager $backups, AuditLogger $audit): int
    {
        $archive = $this->argument('archive') ?: $backups->latestPath();
        if (! $archive) {
            $this->error('No backup archive is available to verify.');

            return self::FAILURE;
        }

        try {
            $manifest = $backups->verify($archive);
            $audit->record('backup', 'backup.verified', 'Verified a system backup.', metadata: [
                'path' => $archive,
                'created_at' => $manifest['created_at'],
            ]);
            $this->info("Backup verified: {$archive}");

            return self::SUCCESS;
        } catch (Throwable $exception) {
            report($exception);
            $audit->record('backup', 'backup.verification_failed', 'System backup verification failed.', metadata: [
                'path' => $archive,
                'error' => $exception->getMessage(),
            ]);
            $this->error('Backup verification failed: '.$exception->getMessage());

            return self::FAILURE;
        }
    }
}
