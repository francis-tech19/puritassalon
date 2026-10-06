<?php

// Create necessary storage directories in /tmp for serverless environment
$storageDirs = [
    '/tmp/storage/framework/views',
    '/tmp/storage/framework/cache',
    '/tmp/storage/framework/sessions',
    '/tmp/storage/logs',
];

foreach ($storageDirs as $dir) {
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
}

// Copy SQLite database to /tmp so it is writable in serverless environment
$sqliteSource = __DIR__ . '/../database/database.sqlite';
$sqliteDest = '/tmp/database.sqlite';

if (file_exists($sqliteSource) && !file_exists($sqliteDest)) {
    copy($sqliteSource, $sqliteDest);
} elseif (!file_exists($sqliteDest)) {
    touch($sqliteDest);
}

putenv("DB_DATABASE={$sqliteDest}");
$_ENV['DB_DATABASE'] = $sqliteDest;
$_SERVER['DB_DATABASE'] = $sqliteDest;

require __DIR__ . '/../public/index.php';

