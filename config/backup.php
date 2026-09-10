<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Database Backups
    |--------------------------------------------------------------------------
    |
    | 'disk' defaults to the private local disk (storage/app/private) rather
    | than 'public' — a DB dump must never be reachable over the web. Point
    | it at 's3' (already configured in config/filesystems.php) once an
    | AWS_BUCKET is set, so backups survive an EB instance being replaced.
    |
    */

    'disk' => env('BACKUP_DISK', 'local'),

    'path' => env('BACKUP_PATH', 'backups'),

    'retention_days' => env('BACKUP_RETENTION_DAYS', 7),

];
