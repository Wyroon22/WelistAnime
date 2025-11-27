<?php
header('Content-Type: application/json; charset=utf-8');

// ตรวจว่า q ถูกส่งมาหรือยัง
if (!isset($_GET['q']) || trim($_GET['q']) === '') {
    echo json_encode([
        "status" => "error",
        "message" => "Query 'q' is required"
    ]);
    exit;
}

$q = urlencode($_GET['q']);
$url = "https://api.jikan.moe/v4/anime?q={$q}&limit=10";

// ดึงข้อมูลจาก Jikan API
$response = @file_get_contents($url);

if ($response === FALSE) {
    echo json_encode([
        "status" => "error",
        "message" => "Failed to fetch from Jikan API"
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// ส่งข้อมูลกลับแบบ JSON 
echo $response;
