<?php

require_once __DIR__ . '/../vendor/autoload.php';

// Load environment variables
$dotenv = Dotenv\Dotenv::createImmutable(dirname(__DIR__));

try {
    $dotenv->load();
} catch (Dotenv\Exception\InvalidPathException $e) {
    error_log($e->getMessage());
    die("Application configuration error.");
}


// Validate required environment variables
try {
    $dotenv->required([
        'DB_HOST',
        'DB_NAME',
        'DB_USER',
        'DB_PASS'
    ]);
} catch (Dotenv\Exception\ValidationException $e) {
    error_log($e->getMessage());
    die("Application configuration error.");
}

// Retrieve database credentials
$host = $_ENV['DB_HOST'];
$dbname = $_ENV['DB_NAME'];
$username = $_ENV['DB_USER'];
$password = $_ENV['DB_PASS'];

// Create database connection
$dsn = "mysql:host=$host;dbname=$dbname;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $username, $password);

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    error_log($e->getMessage());
    die("Database connection failed.");
}
