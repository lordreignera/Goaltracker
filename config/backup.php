<?php

return [
    'enabled' => env('BACKUP_ENABLED', true),
    'disk' => env('BACKUP_DISK', 'local'),
    'path' => trim(env('BACKUP_PATH', 'backups'), '/'),
    'include_evidence' => env('BACKUP_INCLUDE_EVIDENCE', true),
    'retention_days' => (int) env('BACKUP_RETENTION_DAYS', 30),
    'maximum_backups' => (int) env('BACKUP_MAXIMUM_COUNT', 30),
    'restore_test_database' => env('BACKUP_RESTORE_TEST_DATABASE'),
    'binaries' => [
        'mysqldump' => env('MYSQLDUMP_BINARY', 'mysqldump'),
        'mysql' => env('MYSQL_BINARY', 'mysql'),
        'pg_dump' => env('PG_DUMP_BINARY', 'pg_dump'),
        'psql' => env('PSQL_BINARY', 'psql'),
    ],
];
