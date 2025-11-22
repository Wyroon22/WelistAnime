<?php
require_once 'db.php';
require_once 'functions.php';

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare('SELECT id, username, email, password_hash FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password_hash'])) {
        $_SESSION['user'] = ['id' => $user['id'], 'email' => $user['email'], 'username' => $user['username']];
        header('Location: index.php');
        exit;
    } else {
        $error = 'อีเมลหรือรหัสผ่านไม่ถูกต้อง';
    }
}
?>
<!doctype html>
<html lang="th">
<head><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>เข้าสู่ระบบ</title><link rel="stylesheet" href="styles.css"></head>
<body>
<div class="wrap">
    <h2>เข้าสู่ระบบ</h2>
    <?php if ($error) echo '<p class="err">'.h($error).'</p>'; ?>
    <form method="post" class="form-card">
        <input name="email" placeholder="อีเมล" value="<?= h($_POST['email'] ?? '') ?>">
        <input name="password" type="password" placeholder="รหัสผ่าน">
        <button type="submit">เข้าสู่ระบบ</button>
    </form>
    <p><a href="signup.php">ยังไม่มีบัญชี? สมัคร</a></p>
</div>
</body>
</html>
