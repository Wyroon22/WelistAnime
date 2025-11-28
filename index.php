<?php
// index.php (replace your existing file)
// ต้องมี functions.php ที่มี h(), is_login(), current_user() และ db connection
require_once 'functions.php';

$q = trim($_GET['q'] ?? '');
$results = [];
$jikan_error = false;

if ($q !== '') {
    $url = 'https://api.jikan.moe/v4/anime?q=' . urlencode($q) . '&limit=24';
    $json = @file_get_contents($url);
    if ($json === false) {
        $jikan_error = true;
    } else {
        $resp = json_decode($json, true);
        $results = $resp['data'] ?? [];
    }
}
?>
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Anime Info Finder</title>

    <!-- Google font (optional) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Mitr:wght@200;300;400;500;600;700&family=Prompt:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&family=Raleway:ital,wght@0,100..900;1,100..900&family=Sriracha&display=swap" rel="stylesheet">

    <!-- Your custom CSS -->
    <link rel="stylesheet" href="styles.css">
    <style>
    /* small helpers if your styles.css missing something */
    .grid{ margin-top:18px; display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:14px }
    .card{ background:#fff;border-radius:10px;border:1px solid #eef3f7; overflow:hidden; box-shadow:0 6px 18px rgba(17,27,38,0.04) }
    .card img{ width:100%; height:260px; object-fit:cover; display:block; background:#f6f7f8 }
    .card .card-body{ padding:12px 14px; display:flex;flex-direction:column; gap:8px }
    .title{ margin:0; font-weight:600; color:#213042; font-size:1rem }
    .meta{ color:#7b8790; font-size:0.88rem }
    .actions{ display:flex; gap:8px; align-items:center; margin-top:6px }
    .btn{ padding:8px 12px; border-radius:8px; background:#2b3b4a; color:white; border:none; cursor:pointer }
    .btn.ghost{ background:transparent; color:#2b3b4a; border:1px solid #e6eaef }
    .err{ color:#b00020; padding:12px 0 }
    </style>
</head>
<body>
    <div class="wrap" style="max-width:980px;margin:36px auto;padding:20px;font-family:Poppins,system-ui,Arial;">
    <header class="top" style="display:flex;align-items:center;justify-content:space-between;padding:16px;border-radius:12px;background:#fff;box-shadow:0 6px 20px rgba(24,35,50,0.06);">
        <h1 style="margin:0;font-size:1.3rem;">🎌 WeListAnime</h1>
        <nav style="font-size:0.95rem">
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
        <section class="search-box" style="margin-top:20px;background:#fff;border-radius:12px;padding:18px;box-shadow:0 8px 24px rgba(24,35,50,0.04);">
            <form action="index.php" method="get" class="search-form" style="display:flex;gap:10px;align-items:center;">
            <input name="q" placeholder="พิมพ์ชื่ออนิเมะที่ต้องการค้นหา" required value="<?= h($q) ?>" style="flex:1;padding:12px 14px;border-radius:10px;border:1px solid #e6eaef;font-size:1rem">
            <button type="submit" class="btn">ค้นหา</button>


        </form>
        <p class="hint" style="margin-top:10px;color:#7b8790">Search powered by Jikan API — เรียกจากฝั่งเซิร์ฟเวอร์</p>
        </section>

        <?php if ($q !== ''): ?>
            <?php if ($jikan_error): ?>
            <p class="err">ไม่สามารถติดต่อ Jikan API ได้ โปรดลองใหม่ภายหลัง</p>
        <?php else: ?>
            <?php if (count($results) === 0): ?>
            <p class="err">ไม่พบผลลัพธ์สำหรับ: <?= h($q) ?></p>
            <?php else: ?>
            <section class="grid" id="resultsGrid">
                <?php foreach ($results as $it): 
                $img = '';
                if (!empty($it['images']['jpg']['image_url'])) $img = $it['images']['jpg']['image_url'];
                elseif (!empty($it['images']['webp']['image_url'])) $img = $it['images']['webp']['image_url'];
                elseif (!empty($it['image_url'])) $img = $it['image_url'];
                $title = $it['title'] ?? '';
                $mal_id = (int)($it['mal_id'] ?? 0);
                $score = $it['score'] ?? 'N/A';
                ?>
                <article class="card">
                    <img src="<?= h($img) ?>" alt="<?= h($title) ?>" onerror="this.src='placeholder.png'">
                    <div class="card-body">
                        <h3 class="title"><?= h($title) ?></h3>
                        <div class="meta"><?= h($it['type'] ?? '') ?> • Score: <?= h($score) ?></div>
                        <div class="actions">
                        <button class="btn ghost add-fav" 
                        data-mal="<?= $mal_id ?>" 
                        data-title="<?= h($title) ?>" 
                        data-img="<?= h($img) ?>">
                        Add favorite
                        </button>

                        <a class="btn" href="https://myanimelist.net/anime/<?= $mal_id ?>" target="_blank" rel="noopener">MAL</a>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </section>
            <?php endif; ?>
        <?php endif; ?>
        <?php endif; ?>

    </main>

    <footer class="foot" style="text-align:center;margin-top:36px;color:#7b8790">Made with ♥ — Anime Finder PHP Demo</footer>
    </div>

    <!-- small JS: ส่ง add favorite เป็น JSON ไปที่ api/favorites_add.php (AJAX) -->
    <script>
        document.addEventListener('click', function(e){
        const btn = e.target.closest('.add-fav');
        if (!btn) return;
        e.preventDefault();

      // ถ้าไม่ล็อกอิน จะส่งบอกผู้ใช้
      // (เราไม่มี is_login แบบฝั่ง client, แต่ server จะตอบ error หากไม่ได้ล็อกอิน)
        const mal_id = parseInt(btn.dataset.mal || 0, 10);
        const title = btn.dataset.title || '';
        const image_url = btn.dataset.img || '';

        btn.disabled = true;
        const payload = { mal_id, title, image_url };

        fetch('api/favorites_add.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload),
        credentials: 'same-origin'
        }).then(r => r.json())
        .then(j => {
        if (j && j.status === 'success') {
            alert('Added to favorites ✅');
        } else {
            alert('ไม่สามารถเพิ่มรายการโปรด: ' + (j?.message || 'เกิดข้อผิดพลาด'));
        }
        }).catch(err => {
            console.error(err);
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
        }).finally(()=> {
            btn.disabled = false;
        });
    });
    </script>
</body>
</html>







