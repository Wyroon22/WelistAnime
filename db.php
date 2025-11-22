<?php
// db.php
// ตั้งค่าเชื่อมต่อ DB ที่นี่
$DB_HOST = '127.0.0.1';
$DB_NAME = 'anime_app';
$DB_USER = 'anime_user';    
$DB_PASS = 'your_strong_password';

$dsn = "mysql:host=$DB_HOST;dbname=$DB_NAME;charset=utf8mb4";
$options = [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
];

try {
    $pdo = new PDO($dsn, $DB_USER, $DB_PASS, $options);
} catch (Exception $e) {
    die("DB connection failed: " . $e->getMessage());
}
