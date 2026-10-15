<?php

namespace App\Services;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Process\Process;
use ZipArchive;

class BackupManager
{
    public function create(): array
    {
        $this->ensureZipSupport();

        $createdAt = now();
        $temporaryDirectory = storage_path('app/private/backup-temp/'.Str::uuid());
        $archiveName = sprintf(
            '%s/smart-goals-%s-%s.zip',
            config('backup.path'),
            $createdAt->format('Ymd-His'),
            Str::lower(Str::random(8))
        );

        if (! mkdir($temporaryDirectory, 0700, true) && ! is_dir($temporaryDirectory)) {
            throw new RuntimeException('The temporary backup directory could not be created.');
        }

        try {
            $databaseFile = $this->dumpDatabase($temporaryDirectory);
            $checksums = [
                basename($databaseFile) => hash_file('sha256', $databaseFile),
            ];
            $evidenceFiles = $this->copyEvidence($temporaryDirectory, $checksums);
            $manifest = [
                'format_version' => 1,
                'created_at' => $createdAt->toIso8601String(),
                'application' => config('app.name'),
                'environment' => app()->environment(),
                'database_driver' => DB::connection()->getDriverName(),
                'database_file' => basename($databaseFile),
                'evidence_files' => $evidenceFiles,
                'checksums' => $checksums,
            ];

            file_put_contents(
                $temporaryDirectory.'/manifest.json',
                json_encode($manifest, JSON_PRETTY_PRINT | JSON_THROW_ON_ERROR)
            );

            $localArchive = $temporaryDirectory.'/backup.zip';
            $this->writeArchive($localArchive, $temporaryDirectory, array_keys($checksums));
            $this->storeArchive($localArchive, $archiveName);
            $this->prune();

            return [
                'path' => $archiveName,
                'size' => $this->disk()->size($archiveName),
                'manifest' => $manifest,
            ];
        } finally {
            $this->deleteDirectory($temporaryDirectory);
        }
    }

    public function verify(string $archivePath): array
    {
        $this->ensureZipSupport();
        [$localArchive, $removeLocalCopy] = $this->localArchiveCopy($archivePath);
        $zip = new ZipArchive;

        try {
            if ($zip->open($localArchive, ZipArchive::CHECKCONS) !== true) {
                throw new RuntimeException('The backup archive is unreadable or corrupt.');
            }

            $manifestJson = $zip->getFromName('manifest.json');
            if ($manifestJson === false) {
                throw new RuntimeException('The backup archive does not contain a manifest.');
            }

            $manifest = json_decode($manifestJson, true, flags: JSON_THROW_ON_ERROR);
            foreach ($manifest['checksums'] ?? [] as $path => $expectedHash) {
                $contents = $zip->getFromName($path);
                if ($contents === false || ! hash_equals($expectedHash, hash('sha256', $contents))) {
                    throw new RuntimeException("Backup checksum validation failed for [{$path}].");
                }
            }

            if (($manifest['checksums'] ?? []) === []) {
                throw new RuntimeException('The backup manifest does not contain checksums.');
            }

            return $manifest;
        } finally {
            if ($zip->status === ZipArchive::ER_OK) {
                $zip->close();
            }

            if ($removeLocalCopy && is_file($localArchive)) {
                unlink($localArchive);
            }
        }
    }

    public function restore(string $archivePath, string $targetDatabase): array
    {
        $manifest = $this->verify($archivePath);
        $configuredDatabase = (string) config('database.connections.'.config('database.default').'.database');

        if ($targetDatabase === '' || $targetDatabase === $configuredDatabase) {
            throw new RuntimeException('Restoration must target a separate, non-production database.');
        }

        $temporaryDirectory = storage_path('app/private/backup-restore/'.Str::uuid());
        if (! mkdir($temporaryDirectory, 0700, true) && ! is_dir($temporaryDirectory)) {
            throw new RuntimeException('The temporary restore directory could not be created.');
        }

        try {
            $databaseFile = $this->extractDatabaseFile($archivePath, $manifest, $temporaryDirectory);
            $this->restoreDatabase($databaseFile, $targetDatabase, $manifest['database_driver']);

            return $manifest;
        } finally {
            $this->deleteDirectory($temporaryDirectory);
        }
    }

    public function latestPath(): ?string
    {
        return collect($this->disk()->files(config('backup.path')))
            ->filter(fn (string $path) => str_ends_with($path, '.zip'))
            ->sortByDesc(fn (string $path) => $this->disk()->lastModified($path))
            ->first();
    }

    public function prune(): int
    {
        $files = collect($this->disk()->files(config('backup.path')))
            ->filter(fn (string $path) => str_ends_with($path, '.zip'))
            ->sortByDesc(fn (string $path) => $this->disk()->lastModified($path))
            ->values();
        $cutoff = now()->subDays(max(1, (int) config('backup.retention_days')))->timestamp;
        $maximum = max(1, (int) config('backup.maximum_backups'));
        $deleted = 0;

        foreach ($files as $index => $path) {
            if ($index >= $maximum || $this->disk()->lastModified($path) < $cutoff) {
                $deleted += $this->disk()->delete($path) ? 1 : 0;
            }
        }

        return $deleted;
    }

    private function dumpDatabase(string $directory): string
    {
        $connectionName = config('database.default');
        $connection = config("database.connections.{$connectionName}");
        $driver = $connection['driver'];

        return match ($driver) {
            'sqlite' => $this->dumpSqlite($connection, $directory),
            'mysql', 'mariadb' => $this->dumpMysql($connection, $directory),
            'pgsql' => $this->dumpPostgres($connection, $directory),
            default => throw new RuntimeException("Database backups are not supported for [{$driver}]."),
        };
    }

    private function dumpSqlite(array $connection, string $directory): string
    {
        $source = $connection['database'];
        if ($source === ':memory:' || ! is_file($source)) {
            throw new RuntimeException('SQLite backups require a file-based database.');
        }

        DB::connection()->statement('PRAGMA wal_checkpoint(FULL)');
        $destination = $directory.'/database.sqlite';
        if (! copy($source, $destination)) {
            throw new RuntimeException('The SQLite database could not be copied.');
        }

        return $destination;
    }

    private function dumpMysql(array $connection, string $directory): string
    {
        $destination = $directory.'/database.sql';
        $arguments = array_values(array_filter([
            config('backup.binaries.mysqldump'),
            '--host='.$connection['host'],
            '--port='.$connection['port'],
            '--user='.$connection['username'],
            '--single-transaction',
            '--quick',
            '--triggers',
            '--no-tablespaces',
            '--default-character-set='.($connection['charset'] ?? 'utf8mb4'),
            '--result-file='.$destination,
            $connection['database'],
        ]));

        $this->runProcess($arguments, ['MYSQL_PWD' => (string) ($connection['password'] ?? '')]);

        return $destination;
    }

    private function dumpPostgres(array $connection, string $directory): string
    {
        $destination = $directory.'/database.sql';
        $this->runProcess([
            config('backup.binaries.pg_dump'),
            '--host='.$connection['host'],
            '--port='.$connection['port'],
            '--username='.$connection['username'],
            '--format=plain',
            '--file='.$destination,
            $connection['database'],
        ], ['PGPASSWORD' => (string) ($connection['password'] ?? '')]);

        return $destination;
    }

    private function copyEvidence(string $directory, array &$checksums): int
    {
        if (! config('backup.include_evidence')) {
            return 0;
        }

        $disk = Storage::disk(config('filesystems.evidence_disk'));
        $count = 0;
        foreach ($disk->allFiles('weekly-update-evidence') as $path) {
            $contents = $disk->get($path);
            $archivePath = 'evidence/'.$path;
            $localPath = $directory.'/'.$archivePath;
            $parent = dirname($localPath);
            if (! is_dir($parent)) {
                mkdir($parent, 0700, true);
            }
            file_put_contents($localPath, $contents);
            $checksums[$archivePath] = hash('sha256', $contents);
            $count++;
        }

        return $count;
    }

    private function writeArchive(string $archive, string $directory, array $contentPaths): void
    {
        $zip = new ZipArchive;
        if ($zip->open($archive, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new RuntimeException('The backup archive could not be created.');
        }

        $zip->addFile($directory.'/manifest.json', 'manifest.json');
        foreach ($contentPaths as $path) {
            $zip->addFile($directory.'/'.$path, $path);
        }

        if (! $zip->close()) {
            throw new RuntimeException('The backup archive could not be finalized.');
        }
    }

    private function storeArchive(string $localArchive, string $archivePath): void
    {
        $stream = fopen($localArchive, 'rb');
        try {
            if (! $this->disk()->writeStream($archivePath, $stream)) {
                throw new RuntimeException('The backup archive could not be stored.');
            }
        } finally {
            if (is_resource($stream)) {
                fclose($stream);
            }
        }
    }

    private function extractDatabaseFile(string $archivePath, array $manifest, string $directory): string
    {
        [$localArchive, $removeLocalCopy] = $this->localArchiveCopy($archivePath);
        $zip = new ZipArchive;

        try {
            if ($zip->open($localArchive) !== true) {
                throw new RuntimeException('The backup archive could not be opened.');
            }

            $databaseFile = basename((string) $manifest['database_file']);
            $contents = $zip->getFromName($databaseFile);
            if ($contents === false) {
                throw new RuntimeException('The database backup is missing from the archive.');
            }

            $destination = $directory.'/'.$databaseFile;
            file_put_contents($destination, $contents);

            return $destination;
        } finally {
            if ($zip->status === ZipArchive::ER_OK) {
                $zip->close();
            }
            if ($removeLocalCopy && is_file($localArchive)) {
                unlink($localArchive);
            }
        }
    }

    private function restoreDatabase(string $databaseFile, string $targetDatabase, string $driver): void
    {
        $connection = config('database.connections.'.config('database.default'));

        if ($driver === 'sqlite') {
            DB::purge();
            if (! copy($databaseFile, $targetDatabase)) {
                throw new RuntimeException('The SQLite restore target could not be written.');
            }

            return;
        }

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $process = new Process([
                config('backup.binaries.mysql'),
                '--host='.$connection['host'],
                '--port='.$connection['port'],
                '--user='.$connection['username'],
                '--database='.$targetDatabase,
            ], env: ['MYSQL_PWD' => (string) ($connection['password'] ?? '')]);
            $input = fopen($databaseFile, 'rb');
            $process->setInput($input);
            $this->runConfiguredProcess($process);
            fclose($input);

            return;
        }

        if ($driver === 'pgsql') {
            $process = new Process([
                config('backup.binaries.psql'),
                '--host='.$connection['host'],
                '--port='.$connection['port'],
                '--username='.$connection['username'],
                '--dbname='.$targetDatabase,
                '--file='.$databaseFile,
            ], env: ['PGPASSWORD' => (string) ($connection['password'] ?? '')]);
            $this->runConfiguredProcess($process);

            return;
        }

        throw new RuntimeException("Database restoration is not supported for [{$driver}].");
    }

    private function runProcess(array $arguments, array $environment = []): void
    {
        $this->runConfiguredProcess(new Process($arguments, env: $environment));
    }

    private function runConfiguredProcess(Process $process): void
    {
        $process->setTimeout(900);
        $process->run();
        if (! $process->isSuccessful()) {
            throw new RuntimeException(trim($process->getErrorOutput()) ?: 'The database utility failed.');
        }
    }

    private function localArchiveCopy(string $archivePath): array
    {
        if (! $this->disk()->exists($archivePath)) {
            throw new RuntimeException("Backup [{$archivePath}] does not exist.");
        }

        try {
            return [$this->disk()->path($archivePath), false];
        } catch (\Throwable) {
            $temporaryFile = tempnam(storage_path('app/private'), 'backup-');
            file_put_contents($temporaryFile, $this->disk()->get($archivePath));

            return [$temporaryFile, true];
        }
    }

    private function disk(): FilesystemAdapter
    {
        return Storage::disk(config('backup.disk'));
    }

    private function ensureZipSupport(): void
    {
        if (! class_exists(ZipArchive::class)) {
            throw new RuntimeException('The PHP ZIP extension is required for backups.');
        }
    }

    private function deleteDirectory(string $directory): void
    {
        if (! is_dir($directory)) {
            return;
        }

        $items = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($directory, \FilesystemIterator::SKIP_DOTS),
            \RecursiveIteratorIterator::CHILD_FIRST
        );
        foreach ($items as $item) {
            $item->isDir() ? rmdir($item->getPathname()) : unlink($item->getPathname());
        }
        rmdir($directory);
    }
}
