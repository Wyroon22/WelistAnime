<?php
require_once 'db.php';
require_once 'functions.php';

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'อีเมลไม่ถูกต้อง';
    }

    if (strlen($password) < 6) {
        $errors[] = 'รหัสผ่านต้องยาวอย่างน้อย 6 ตัว';
    }

    if (empty($errors)) {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email=?");
        $stmt->execute([$email]);

        if ($stmt->fetch()) {
            $errors[] = "อีเมลนี้ถูกใช้แล้ว";
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)");
            $stmt->execute([$username, $email, $hash]);

            $_SESSION['user'] = [
                'id' => $pdo->lastInsertId(),
                'email' => $email,
                'username' => $username
            ];

            header("Location: index.php");
            exit;
        }
    }
}
?>
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <title>สมัครสมาชิก</title>
    <link rel="stylesheet" href="styles.css">
</head>

<body>
<div class="wrap">
    <h2>สมัครสมาชิก</h2>

    <?php if (!empty($errors)): ?>
        <?php foreach ($errors as $e): ?>
            <p class="err"><?= h($e) ?></p>
        <?php endforeach; ?>
    <?php endif; ?>

    <form method="post" class="form-card">
        <input name="username" placeholder="ชื่อเล่น (ไม่บังคับ)" value="<?= h($_POST['username'] ?? '') ?>">
        <input name="email" placeholder="อีเมล" value="<?= h($_POST['email'] ?? '') ?>">
        <input name="password" type="password" placeholder="รหัสผ่าน">
        <button type="submit">สมัคร</button>
    </form>

    <p><a href="signin.php">มีบัญชีอยู่แล้ว? เข้าสู่ระบบ</a></p>
</div>
</body>
</html>
