<?php
require_once 'db.php';
require_once 'functions.php';
require_login();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: favorites.php');
    exit;
}

$user = current_user();
$mal_id = (int)($_POST['mal_id'] ?? 0);

if ($mal_id) {
    $stmt = $pdo->prepare('DELETE FROM favorites WHERE user_id = ? AND mal_id = ?');
    $stmt->execute([$user['id'], $mal_id]);
}

header('Location: favorites.php');
exit;
