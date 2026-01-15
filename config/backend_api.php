<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Backend API Configuration
    |--------------------------------------------------------------------------
    |
    | バックエンドAPI (backlog_demo) との通信設定
    |
    */

    'base_url' => env('BACKEND_API_URL', 'http://host.docker.internal:8081'),

    'timeout' => env('BACKEND_API_TIMEOUT', 30),

    'retry_times' => env('BACKEND_API_RETRY_TIMES', 2),

    'retry_sleep' => env('BACKEND_API_RETRY_SLEEP', 100), // milliseconds

    /*
    |--------------------------------------------------------------------------
    | Fallback Settings
    |--------------------------------------------------------------------------
    |
    | バックエンドAPI呼び出し失敗時のフォールバック設定
    |
    */

    'fallback_enabled' => env('BACKEND_API_FALLBACK_ENABLED', true),
];
