<?php
require_once 'functions.php';
require_once 'db.php';

$q = trim($_GET['q'] ?? '');
if ($q === '') {
    header('Location: index.php');
    exit;
}

// call Jikan API (search)
$api = 'https://api.jikan.moe/v4/anime?q=' . urlencode($q) . '&limit=24&sfw=true';

$ch = curl_init($api);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_TIMEOUT, 10);
$res = curl_exec($ch);
$http = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

$data = [];
if ($http === 200) {
    $json = json_decode($res, true);
    $data = $json['data'] ?? [];
}

?>
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>ผลการค้นหา: <?= h($q) ?></title>
    <link rel="stylesheet" href="styles.css">
</head>
<body>
<div class="wrap">
    <header class="top">
        <h1>ผลการค้นหา: <?= h($q) ?></h1>
        <nav>
            <a href="index.php">หน้าแรก</a> |
        <?php if (is_login()): ?>
            <a href="favorites.php">รายการโปรด</a> |
            <a href="signout.php">ออกจากระบบ</a>
        <?php else: ?>
            <a href="signin.php">เข้าสู่ระบบ</a>
        <?php endif; ?>
        </nav>
    </header>

    <main>
        <?php if ($http !== 200): ?>
        <p class="err">เกิดข้อผิดพลาดในการเรียก API (HTTP <?= $http ?>)</p>
            <?php elseif (empty($data)): ?>
        <p>ไม่พบผลลัพธ์</p>
        <?php else: ?>
        <div class="grid">
            <?php foreach ($data as $item):
            $mal_id = $item['mal_id'];
            $title = $item['title'] ?? ($item['title_english'] ?? 'Untitled');
            $img = $item['images']['jpg']['image_url'] ?? '';
            $score = $item['score'] ?? '–';
            ?>
            <div class="card">
                <img src="<?= h($img) ?>" alt="<?= h($title) ?>">
                <div class="card-body">
                <h3><?= h($title) ?></h3>
                <p>⭐ <?= h($score) ?></p>
                <div class="actions">
                    <a class="btn" href="<?= h($item['url'] ?? '#') ?>" target="_blank" rel="noreferrer">ดูบน MAL</a>

                    <?php if (is_login()): ?>
                    <form action="favorites_add.php" method="post" style="display:inline">
                        <input type="hidden" name="mal_id" value="<?= h($mal_id) ?>">
                        <input type="hidden" name="title" value="<?= h($title) ?>">
                        <input type="hidden" name="image_url" value="<?= h($img) ?>">
                        <button class="btn" type="submit">♡ เพิ่มในรายการโปรด</button>
                    </form>
                    <?php else: ?>
                    <a class="btn" href="signin.php">ล็อกอินเพื่อเพิ่ม</a>
                    <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
        </div>
    <?php endif; ?>
    </main>

    <footer class="foot"><a href="index.php">กลับ</a></footer>
</div>
</body>
</html>
