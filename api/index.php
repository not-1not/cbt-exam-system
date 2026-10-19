<?php

// 1. Ensure required temporary storage directories exist for Vercel Serverless environment
$dirs = [
    '/tmp/storage/app/public',
    '/tmp/storage/framework/cache/data',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/framework/views',
    '/tmp/storage/logs',
    '/tmp/bootstrap/cache',
];

foreach ($dirs as $dir) {
    if (! is_dir($dir)) {
        @mkdir($dir, 0755, true);
    }
}

// 2. Auto-initialize SQLite database in /tmp if running in SQLite mode and not yet present
$dbConnection = getenv('DB_CONNECTION') ?: ($_ENV['DB_CONNECTION'] ?? 'sqlite');
$dbDatabase = getenv('DB_DATABASE') ?: ($_ENV['DB_DATABASE'] ?? '/tmp/database.sqlite');

if ($dbConnection === 'sqlite') {
    $seedSource = __DIR__ . '/../database/seed.sqlite';
    if (! file_exists($dbDatabase) && file_exists($seedSource)) {
        @copy($seedSource, $dbDatabase);
    }
}

// 3. Delegate incoming HTTP request to Laravel's standard entry point
require __DIR__ . '/../public/index.php';
