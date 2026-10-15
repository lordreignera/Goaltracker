<?php

namespace App\Console\Commands;

use App\Models\WeeklyUpdate;
use App\Services\AuditLogger;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Throwable;

class PruneEvidence extends Command
{
    protected $signature = 'evidence:prune';

    protected $description = 'Remove evidence files that have exceeded the configured retention period';

    public function handle(AuditLogger $audit): int
    {
        if (! config('evidence.retention_enabled')) {
            $this->warn('Evidence retention pruning is disabled.');

            return self::SUCCESS;
        }

        $cutoff = now()->subDays(max(1, (int) config('evidence.retention_days')));
        $deleted = 0;
        $disk = Storage::disk(config('filesystems.evidence_disk'));

        WeeklyUpdate::query()
            ->whereNotNull('evidence_path')
            ->whereNull('evidence_deleted_at')
            ->where('created_at', '<', $cutoff)
            ->chunkById(100, function ($updates) use ($disk, &$deleted) {
                foreach ($updates as $update) {
                    try {
                        $disk->delete($update->evidence_path);
                        $update->update([
                            'evidence_path' => null,
                            'evidence_original_name' => null,
                            'evidence_deleted_at' => now(),
                        ]);
                        $deleted++;
                    } catch (Throwable $exception) {
                        report($exception);
                        $this->error("Could not prune evidence for report [{$update->id}].");
                    }
                }
            });

        $audit->record('reporting', 'evidence.pruned', 'Pruned evidence files past their retention period.', metadata: [
            'cutoff' => $cutoff->toIso8601String(),
            'files_deleted' => $deleted,
        ]);
        $this->info("Pruned {$deleted} evidence file(s).");

        return self::SUCCESS;
    }
}
