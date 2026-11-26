<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Laravel Cloud API
    |--------------------------------------------------------------------------
    |
    | The API token is created in the Laravel Cloud dashboard under your
    | organization settings. It is used to poll the status of each target
    | environment. Status checks are disabled by default because the API
    | currently reports environments that scaled to zero as "running", so
    | the calls only add latency. Flip the flag once the API reports real
    | statuses.
    |
    */

    'cloud_status_enabled' => env('GAME_CLOUD_STATUS_ENABLED', false),

    'cloud_api_token' => env('LARAVEL_CLOUD_API_TOKEN'),

    'cloud_base_url' => env('LARAVEL_CLOUD_BASE_URL', 'https://cloud.laravel.com'),

    /*
    |--------------------------------------------------------------------------
    | Game Tuning
    |--------------------------------------------------------------------------
    |
    | Statuses are cached for a few seconds so the UI may poll every five
    | seconds without hammering the Cloud API. A round that never finishes
    | loading is voided after the timeout below.
    |
    */

    'status_cache_ttl' => 8,

    'round_timeout_ms' => 10000,

    /*
    | A round whose wake takes longer than this is voided instead of scored.
    | Wakes measure 300–500 ms from Frankfurt, so anything this slow is a hiccup.
    */

    'max_wake_ms' => env('GAME_MAX_WAKE_MS', 2000),

    'server_region' => env('GAME_SERVER_REGION', 'Frankfurt'),

    /*
    |--------------------------------------------------------------------------
    | Wake Cooldown
    |--------------------------------------------------------------------------
    |
    | The Cloud API does not reliably report scale-to-zero sleep, so the game
    | tracks wake-ups itself. A target only becomes playable again once this
    | many seconds have passed since it was last clicked, giving the platform
    | time to put it back to sleep.
    |
    */

    'wake_cooldown' => env('GAME_WAKE_COOLDOWN', 150),

    /*
    |--------------------------------------------------------------------------
    | Nudge Interval
    |--------------------------------------------------------------------------
    |
    | A target that has slept for a long time wakes noticeably slower than one
    | that fell asleep a few minutes ago. The scheduled targets:nudge command
    | wakes any target idle for this many seconds, so players always race an
    | app that went to sleep recently. Must be longer than the wake cooldown.
    |
    */

    'nudge_after' => env('GAME_NUDGE_AFTER', 300),

    /*
    |--------------------------------------------------------------------------
    | Target Applications
    |--------------------------------------------------------------------------
    |
    | The scale-to-zero Laravel Cloud applications players race against. Each
    | target needs its public URL plus the application and environment IDs
    | from the Laravel Cloud API.
    |
    */

    'targets' => [
        [
            'name' => 'Frankfurt 1',
            'url' => 'https://scale-to-zero-target-1-production-xaiici.laravel.cloud',
            'application_id' => 'app-a33f46b6-72ed-47b0-9ded-449bb0a1787d',
            'environment_id' => 'env-a33f46bc-cf7d-4bfc-8a33-45a23d7971c8',
        ],
        [
            'name' => 'Frankfurt 2',
            'url' => 'https://scale-to-zero-target-2-production-r1t1s6.laravel.cloud',
            'application_id' => 'app-a33f4702-420f-4ab4-af31-3b2e7257ef2d',
            'environment_id' => 'env-a33f4705-540d-414f-9de7-fef1ea739159',
        ],
        [
            'name' => 'Frankfurt 3',
            'url' => 'https://scale-to-zero-target-3-production-p7uwxk.laravel.cloud',
            'application_id' => 'app-a33f4710-a14e-418d-96a2-d2b60e386d47',
            'environment_id' => 'env-a33f4713-7ba9-42a6-87c6-a572279c696e',
        ],
        [
            'name' => 'Frankfurt 4',
            'url' => 'https://scale-to-zero-target-4-production-kry72q.laravel.cloud',
            'application_id' => 'app-a33f471f-6305-41df-a1ac-0ed800f84129',
            'environment_id' => 'env-a33f4722-7116-4503-b6d3-4b31a64e557f',
        ],
        [
            'name' => 'Frankfurt 5',
            'url' => 'https://scale-to-zero-target-5-production-kvbnkp.laravel.cloud',
            'application_id' => 'app-a33f472e-95d4-44c3-b9d1-feb2a6be565d',
            'environment_id' => 'env-a33f4731-6d1f-443f-8954-95e4990c3d11',
        ],
    ],

];
