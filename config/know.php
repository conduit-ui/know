<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Default Knowledge Store
    |--------------------------------------------------------------------------
    |
    | This option controls the default knowledge store that will be used
    | to persist and retrieve insights. You may choose between 'memory'
    | for in-memory storage (not persistent) or 'database' for persistent
    | storage in your application's database.
    |
    */

    'default_store' => env('KNOW_STORE', 'memory'),

    /*
    |--------------------------------------------------------------------------
    | Sync Adapters Configuration
    |--------------------------------------------------------------------------
    |
    | Configure external sources for syncing knowledge. Each adapter
    | can be enabled/disabled and configured with source-specific options.
    |
    */

    'adapters' => [
        'claude-mem' => [
            'enabled' => env('KNOW_CLAUDE_MEM_ENABLED', true),
            'path' => env('KNOW_CLAUDE_MEM_PATH', '~/.claude-mem/claude-mem.db'),
        ],

        'github-pkm' => [
            'enabled' => env('KNOW_GITHUB_PKM_ENABLED', false),
            'local_path' => env('KNOW_GITHUB_PKM_PATH', '~/personal-knowledge-management'),
            'repository' => env('KNOW_GITHUB_PKM_REPO', 'jordanpartridge/personal-knowledge-management'),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Sync Settings
    |--------------------------------------------------------------------------
    |
    | Control sync behavior and scheduling.
    |
    */

    'sync' => [
        // Auto-sync on application boot
        'auto_sync' => env('KNOW_AUTO_SYNC', false),

        // Which adapters to auto-sync from
        'auto_sync_sources' => ['claude-mem'],

        // Sync interval in seconds (for scheduled sync)
        'sync_interval' => env('KNOW_SYNC_INTERVAL', 3600), // 1 hour
    ],
];
