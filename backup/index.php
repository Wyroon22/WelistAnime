<?php
require_once 'functions.php';
?>
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Anime Info Finder</title>
    <link rel="stylesheet" href="styles.css">
    <link>
</head>
<body>
    <div class="wrap">
        <header class="top">
        <h1>🎌 Anime Info Finder (PHP)</h1>
        <nav>
            <?php if (is_login()): ?>
                สวัสดี, <?= h(current_user()['username'] ?: current_user()['email']) ?> |
                <a href="favorites.php">รายการโปรด</a> |
                <a href="signout.php">ออกจากระบบ</a>
                <?php else: ?>
                <a href="auth.php?show=signup">สมัคร</a> |
                <a href="auth.php?show=signin">เข้าสู่ระบบ</a>
                <?php endif; ?>
            </nav>
        </header>

        <main>
        <section class="search-box">
            <form action="search.php" method="get" class="search-form">
                <input name="q" placeholder="พิมพ์ชื่ออนิเมะ เช่น Naruto" required value="<?= h($_GET['q'] ?? '') ?>">
                <button type="submit">ค้นหา</button>
            </form>
                <p class="hint">Search powered by Jikan API — จะเรียกจากฝั่งเซิร์ฟเวอร์</p>
        </section>
        </main>

    <footer class="foot">Made with ♥ — Anime Finder PHP Demo</footer>
    </div>
</body>
</html>






