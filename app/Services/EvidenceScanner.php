<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use RuntimeException;
use Symfony\Component\Process\ExecutableFinder;
use Symfony\Component\Process\Process;

class EvidenceScanner
{
    public function scan(UploadedFile $file): void
    {
        if (! config('evidence.scan_enabled')) {
            return;
        }

        $binary = (string) config('evidence.scanner_binary');
        if ((new ExecutableFinder)->find($binary) === null) {
            throw new RuntimeException('The evidence security scanner is unavailable. Upload was rejected.');
        }

        try {
            $process = new Process([
                $binary,
                '--no-summary',
                '--infected',
                '--stdout',
                '--',
                $file->getRealPath(),
            ]);
            $process->setTimeout(config('evidence.scan_timeout'));
            $process->run();
        } catch (\Throwable $exception) {
            throw new RuntimeException('The evidence security scanner is unavailable. Upload was rejected.', 0, $exception);
        }

        if ($process->getExitCode() === 1) {
            throw new RuntimeException('The evidence file was rejected because malware was detected.');
        }

        if (! $process->isSuccessful()) {
            throw new RuntimeException('The evidence security scanner failed. Upload was rejected.');
        }
    }
}
