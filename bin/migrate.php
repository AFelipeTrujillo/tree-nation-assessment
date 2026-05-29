<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

$root = dirname(__DIR__);

if (file_exists($root . '/.env')) {
    Dotenv::createImmutable($root)->safeLoad();
}

$databasePath = $_ENV['DATABASE_PATH'] ?? 'var/app.sqlite';
$absoluteDatabasePath = str_starts_with($databasePath, '/')
    ? $databasePath
    : $root . '/' . $databasePath;

$databaseDirectory = dirname($absoluteDatabasePath);
if (! is_dir($databaseDirectory)) {
    mkdir($databaseDirectory, 0777, true);
}

$schemaPath = $root . '/database/schema.sql';
$schema = file_get_contents($schemaPath);

if ($schema === false) {
    throw new RuntimeException('Could not read database/schema.sql');
}

$pdo = new PDO('sqlite:' . $absoluteDatabasePath);
$pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$pdo->exec($schema);

fwrite(STDOUT, "Database schema initialized at {$absoluteDatabasePath}\n");
