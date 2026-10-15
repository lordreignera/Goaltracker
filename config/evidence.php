<?php

return [
    'scan_enabled' => env('EVIDENCE_SCAN_ENABLED', env('APP_ENV') === 'production'),
    'scanner_binary' => env('EVIDENCE_SCANNER_BINARY', 'clamscan'),
    'scan_timeout' => (int) env('EVIDENCE_SCAN_TIMEOUT', 120),
    'retention_enabled' => env('EVIDENCE_RETENTION_ENABLED', true),
    'retention_days' => (int) env('EVIDENCE_RETENTION_DAYS', 365),
];
