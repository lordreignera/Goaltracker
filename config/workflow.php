<?php

return [
    'notification_channels' => array_values(array_filter(array_map(
        'trim',
        explode(',', env('WORKFLOW_NOTIFICATION_CHANNELS', 'database'))
    ))),
    'overdue_reminders_enabled' => env('OVERDUE_REPORT_REMINDERS_ENABLED', true),
];
