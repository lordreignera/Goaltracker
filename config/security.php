<?php

return [
    // MFA setup remains available, but application-wide enforcement is opt-in and disabled by default.
    'require_mfa' => env('SECURITY_REQUIRE_MFA', false),
    'force_https' => env('SECURITY_FORCE_HTTPS', env('APP_ENV') === 'production'),
    'hsts' => env('SECURITY_HSTS', env('APP_ENV') === 'production'),
    'hsts_max_age' => (int) env('SECURITY_HSTS_MAX_AGE', 31536000),
    'trusted_proxies' => array_values(array_filter(array_map(
        'trim',
        explode(',', (string) env('TRUSTED_PROXIES', ''))
    ))),
];
