<?php
require_once 'db.php';
require_once 'functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: index.php');
    exit;
}

$user = current_user();
$mal_id = (int)($_POST['mal_id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$image_url = trim($_POST['image_url'] ?? '');

if (!$mal_id) {
    header('Location: index.php');
    exit;
}

try {
    // INSERT IGNORE: ถ้ามีแล้วจะข้าม (MySQL)
    $stmt = $pdo->prepare('INSERT IGNORE INTO favorites (user_id, mal_id, title, image_url) VALUES (?, ?, ?, ?)');
    $stmt->execute([$user['id'], $mal_id, $title ?: null, $image_url ?: null]);
} catch (Exception $e) {
    // ignore on purpose for demo
}

header('Location: favorites.php');
exit;
