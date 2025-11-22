<?php
require_once 'db.php';
require_once 'functions.php';
require_login();

$user = current_user();
$stmt = $pdo->prepare('SELECT mal_id, title, image_url, created_at FROM favorites WHERE user_id = ? ORDER BY created_at DESC');
$stmt->execute([$user['id']]);
$favs = $stmt->fetchAll();
?>
<!doctype html>
<html lang="th">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>รายการโปรด</title><link rel="stylesheet" href="styles.css"></head>
<body>
<div class="wrap">
    <header class="top">
        <h1>รายการโปรดของ <?= h($user['username'] ?: $user['email']) ?></h1>
        <nav><a href="index.php">หน้าแรก</a> | <a href="signout.php">ออก</a></nav>
    </header>

    <main>
        <?php if (empty($favs)): ?>
        <p>ยังไม่มีรายการโปรด — ไปค้นหาแล้วเพิ่มได้เลย</p>
        <?php else: ?>
            <div class="grid">
                <?php foreach ($favs as $f): ?>
            <div class="card">
                <img src="<?= h($f['image_url']) ?>" alt="<?= h($f['title']) ?>">
            <div class="card-body">
                <h3 h3><?= h($f['title']) ?></h3>
                <form action="favorites_del.php" method="post" onsubmit="return confirm('ต้องการลบใช่หรือไม่?')">
                    <input type="hidden" name="mal_id" value="<?= h($f['mal_id']) ?>">
                    <button class="btn" type="submit">ลบ</button>
                </form>
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
