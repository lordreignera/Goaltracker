<?php

namespace Tests\Feature;

use App\Services\BackupManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PDO;
use RuntimeException;
use Tests\TestCase;
use ZipArchive;

class BackupManagerTest extends TestCase
{
    private array $temporaryDatabases = [];

    protected function tearDown(): void
    {
        DB::purge('backup_testing');

        foreach ($this->temporaryDatabases as $path) {
            if (is_file($path)) {
                unlink($path);
            }
        }

        parent::tearDown();
    }

    public function test_backup_is_verified_and_can_be_restored_into_a_separate_database(): void
    {
        [$source, $target] = $this->configureFileDatabase();
        Storage::fake('backup-test');
        Storage::fake('evidence-test');
        Storage::disk('evidence-test')->put('weekly-update-evidence/proof.txt', 'verified evidence');

        DB::statement('CREATE TABLE migrations (id INTEGER PRIMARY KEY, migration VARCHAR(255))');
        DB::statement("INSERT INTO migrations (id, migration) VALUES (1, 'create_users_table')");

        $manager = app(BackupManager::class);
        $backup = $manager->create();
        $manifest = $manager->verify($backup['path']);
        $manager->restore($backup['path'], $target);

        Storage::disk('backup-test')->assertExists($backup['path']);
        $this->assertSame('sqlite', $manifest['database_driver']);
        $this->assertSame(1, $manifest['evidence_files']);
        $this->assertArrayHasKey('database.sqlite', $manifest['checksums']);
        $this->assertArrayHasKey('evidence/weekly-update-evidence/proof.txt', $manifest['checksums']);

        $restored = new PDO('sqlite:'.$target);
        $this->assertSame('create_users_table', $restored->query('SELECT migration FROM migrations')->fetchColumn());
        $this->assertNotSame($source, $target);
    }

    public function test_checksum_verification_rejects_a_tampered_archive(): void
    {
        $this->configureFileDatabase();
        Storage::fake('backup-test');
        Storage::fake('evidence-test');
        DB::statement('CREATE TABLE migrations (id INTEGER PRIMARY KEY, migration VARCHAR(255))');

        $manager = app(BackupManager::class);
        $backup = $manager->create();
        $archive = Storage::disk('backup-test')->path($backup['path']);
        $zip = new ZipArchive;
        $zip->open($archive);
        $zip->addFromString('database.sqlite', 'tampered');
        $zip->close();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('checksum validation failed');

        $manager->verify($backup['path']);
    }

    public function test_restore_refuses_to_overwrite_the_configured_database(): void
    {
        [$source] = $this->configureFileDatabase();
        Storage::fake('backup-test');
        Storage::fake('evidence-test');
        DB::statement('CREATE TABLE migrations (id INTEGER PRIMARY KEY, migration VARCHAR(255))');

        $manager = app(BackupManager::class);
        $backup = $manager->create();

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('separate, non-production database');

        $manager->restore($backup['path'], $source);
    }

    private function configureFileDatabase(): array
    {
        $source = storage_path('framework/testing/backup-source-'.Str::uuid().'.sqlite');
        $target = storage_path('framework/testing/backup-target-'.Str::uuid().'.sqlite');
        touch($source);
        $this->temporaryDatabases = [$source, $target];

        config([
            'database.default' => 'backup_testing',
            'database.connections.backup_testing' => [
                'driver' => 'sqlite',
                'database' => $source,
                'prefix' => '',
                'foreign_key_constraints' => true,
            ],
            'backup.disk' => 'backup-test',
            'backup.path' => 'backups',
            'backup.include_evidence' => true,
            'backup.retention_days' => 30,
            'backup.maximum_backups' => 30,
            'filesystems.evidence_disk' => 'evidence-test',
        ]);
        DB::purge('backup_testing');

        return [$source, $target];
    }
}
