<?php
declare(strict_types=1);

session_start();

$database = new PDO('sqlite:' . __DIR__ . '/schedule.sqlite');
$database->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
$database->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

$database->exec('CREATE TABLE IF NOT EXISTS schedule (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    week INTEGER NOT NULL,
    day TEXT NOT NULL,
    start_time TEXT NOT NULL,
    title TEXT NOT NULL,
    location TEXT NOT NULL DEFAULT "",
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
)');

$database->exec('CREATE TABLE IF NOT EXISTS users (
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    email TEXT NOT NULL UNIQUE,
    password_hash TEXT NOT NULL,
    created_at TEXT NOT NULL DEFAULT CURRENT_TIMESTAMP
)');

$columns = $database->query('PRAGMA table_info(schedule)')->fetchAll();
$hasUserId = false;
foreach ($columns as $column) {
    if ($column['name'] === 'user_id') {
        $hasUserId = true;
        break;
    }
}
if (!$hasUserId) {
    $database->exec('ALTER TABLE schedule ADD COLUMN user_id INTEGER REFERENCES users(id)');
}

function currentUser(): ?array
{
    return $_SESSION['user'] ?? null;
}

function requireLogin(): array
{
    $user = currentUser();
    if ($user === null) {
        header('Location: index.php');
        exit;
    }
    return $user;
}

function escape(string $value): string
{
    return htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
}