<?php
// api/favorites_delete.php
header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../functions.php'; // ปรับ path ตามโครงโปรเจค
require_once __DIR__ . '/../db.php';        // ต้องมี $pdo

// ต้องล็อกอินก่อน
if (!is_login()) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Not authenticated']);
    exit;
}

$user = current_user();
$user_id = $user['id'] ?? null;
if (!$user_id) {
    http_response_code(401);
    echo json_encode(['status' => 'error', 'message' => 'Invalid user']);
    exit;
}

// อ่าน input — รองรับ DELETE กับ POST (JSON body) เพื่อความสะดวก
$method = $_SERVER['REQUEST_METHOD'];
$input = null;

if ($method === 'DELETE' || $method === 'POST' || $method === 'PUT') {
    $raw = file_get_contents('php://input');
    $json = json_decode($raw, true);
    if (json_last_error() === JSON_ERROR_NONE) {
        $input = $json;
    } else {
        // ถ้าไม่ใช่ JSON ลองอ่าน $_POST (form-data / x-www-form-urlencoded)
        $input = $_POST ?: null;
    }
} else {
    // GET/อื่น ๆ -> ใช้ $_GET เป็น fallback
    $input = $_GET ?: null;
}

$favorite_id = isset($input['favorite_id']) ? (int)$input['favorite_id'] : null;
$mal_id = isset($input['mal_id']) ? (int)$input['mal_id'] : null;

if (!$favorite_id && !$mal_id) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing favorite_id or mal_id']);
    exit;
}

try {
    if ($favorite_id) {
        // ลบโดยใช้ primary id ของตาราง favorites
        $stmt = $pdo->prepare('DELETE FROM favorites WHERE id = ? AND user_id = ?');
        $stmt->execute([$favorite_id, $user_id]);
        $deleted = $stmt->rowCount();
    } else {
        // ลบโดยใช้ mal_id ของอนิเมะ (และ user_id)
        $stmt = $pdo->prepare('DELETE FROM favorites WHERE mal_id = ? AND user_id = ?');
        $stmt->execute([$mal_id, $user_id]);
        $deleted = $stmt->rowCount();
    }

    if ($deleted) {
        echo json_encode(['status' => 'success', 'message' => 'Favorite deleted', 'deleted_rows' => $deleted]);
    } else {
        http_response_code(404);
        echo json_encode(['status' => 'error', 'message' => 'Favorite not found or not owned by user']);
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Server error', 'detail' => $e->getMessage()]);
}