<?php
// favorites_add.php
header("Content-Type: application/json; charset=utf-8");

// ปรับ path ให้ตรงโฟลเดอร์โปรเจ็กต์ของเธอ
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../functions.php';

// ต้อง login ก่อน
if (session_status() !== PHP_SESSION_ACTIVE) session_start();
if (!is_login()) {
    http_response_code(401);
    echo json_encode([
        "status" => "error",
        "message" => "You must be logged in"
    ]);
    exit;
}

$user = current_user();
$user_id = (int)$user["id"];

// อ่าน JSON body
$raw = file_get_contents("php://input");
$data = json_decode($raw, true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Invalid JSON"]);
    exit;
}

$mal_id    = (int)($data["mal_id"] ?? 0);
$title     = trim($data["title"] ?? "");
$image_url = trim($data["image_url"] ?? "");

if ($mal_id <= 0 || $title === "") {
    http_response_code(400);
    echo json_encode(["status" => "error", "message" => "Missing fields: mal_id or title"]);
    exit;
}

// พยายาม insert — ถ้าซ้ำให้แจ้ง error (unique: user_id + mal_id)
try {
    $stmt = $pdo->prepare("
        INSERT INTO favorites (user_id, mal_id, title, image_url, created_at)
        VALUES (?, ?, ?, ?, NOW())
    ");
    $stmt->execute([$user_id, $mal_id, $title, $image_url]);

    echo json_encode([
        "status" => "success",
        "message" => "Added to favorites",
        "favorite_id" => $pdo->lastInsertId()
    ]);
    exit;

} catch (PDOException $e) {
    // duplicate error 1062
    if (isset($e->errorInfo[1]) && $e->errorInfo[1] == 1062) {
        http_response_code(409);
        echo json_encode([
            "status" => "error",
            "message" => "This anime is already in favorites"
        ]);
        exit;
    }

    http_response_code(500);
    echo json_encode([
        "status" => "error",
        "message" => "Database error"
    ]);
    exit;
}
