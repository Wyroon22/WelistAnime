<?php
// api/login.php
// รับ JSON { "email": "...", "password": "..." }
// ตอบ JSON { status: "success"|"error", message: "...", user: {...} }
// และเซ็ต $_SESSION['user'] เมื่อ login สำเร็จ

header('Content-Type: application/json; charset=utf-8');

// ปรับ path ถ้าไฟล์ db.php / functions.php อยู่คนละโฟลเดอร์
require_once __DIR__ . '/../db.php';
require_once __DIR__ . '/../functions.php';

http_response_code(200);

// อ่าน JSON body
$raw = file_get_contents('php://input');
$data = json_decode($raw, true);

// ถ้าไม่มี JSON ให้รองรับ form-urlencoded (สำหรับทดสอบง่าย)
if (!is_array($data)) {
    $data = $_POST;
}

$email = trim($data['email'] ?? '');
$password = $data['password'] ?? '';

if ($email === '' || $password === '') {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => 'Missing email or password'
    ]);
    exit;
}

try {
    $stmt = $pdo->prepare('SELECT id, username, email, password_hash FROM users WHERE email = ? LIMIT 1');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if (!$user) {
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid email or password'
        ]);
        exit;
    }

    if (!password_verify($password, $user['password_hash'])) {
        http_response_code(401);
        echo json_encode([
            'status' => 'error',
            'message' => 'Invalid email or password'
        ]);
        exit;
    }

    // login สำเร็จ — ตั้ง session
    // ถ้า functions.php เรียก session_start() แล้วจะโอเค ถ้าไม่ ให้เรียกก่อน
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $_SESSION['user'] = [
        'id' => (int)$user['id'],
        'email' => $user['email'],
        'username' => $user['username']
    ];

    // ตอบข้อมูล user (ไม่ส่ง password_hash)
    echo json_encode([
        'status' => 'success',
        'message' => 'Login successful',
        'user' => [
            'id' => (int)$user['id'],
            'email' => $user['email'],
            'username' => $user['username']
        ]
    ]);
    exit;

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'status' => 'error',
        'message' => 'Server error'
    ]);
    exit;
}