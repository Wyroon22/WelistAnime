<?php
// auth.php
// Requires: db.php (provides $pdo), functions.php (session_start(), h(), is_login(), current_user())

require_once 'db.php';
require_once 'functions.php';

$signin_errors = [];
$signup_errors = [];
$signin_email = '';
$signup_username = '';
$signup_email = '';

// Process POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $form = $_POST['form_type'] ?? '';

    if ($form === 'signin') {
        $signin_email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if ($signin_email === '' || $password === '') {
            $signin_errors[] = 'กรุณากรอกอีเมลและรหัสผ่าน';
        } else {
            try {
                $stmt = $pdo->prepare('SELECT id, username, email, password_hash FROM users WHERE email = ? LIMIT 1');
                $stmt->execute([$signin_email]);
                $user = $stmt->fetch();

                if ($user && password_verify($password, $user['password_hash'])) {
                    // success: set session
                    $_SESSION['user'] = [
                        'id' => $user['id'],
                        'email' => $user['email'],
                        'username' => $user['username']
                    ];
                    header('Location: index.php');
                    exit;
                } else {
                    $signin_errors[] = 'อีเมลหรือรหัสผ่านไม่ถูกต้อง';
                }
            } catch (Exception $e) {
                // for dev show generic message; in prod log error
                $signin_errors[] = 'เกิดข้อผิดพลาดภายในระบบ';
            }
        }
    } elseif ($form === 'signup') {
        $signup_username = trim($_POST['username'] ?? '');
        $signup_email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (!filter_var($signup_email, FILTER_VALIDATE_EMAIL)) {
            $signup_errors[] = 'อีเมลไม่ถูกต้อง';
        }
        if (strlen($password) < 6) {
            $signup_errors[] = 'รหัสผ่านต้องอย่างน้อย 6 ตัวอักษร';
        }

        if (empty($signup_errors)) {
            try {
                $stmt = $pdo->prepare('SELECT id FROM users WHERE email = ? LIMIT 1');
                $stmt->execute([$signup_email]);
                if ($stmt->fetch()) {
                    $signup_errors[] = 'อีเมลนี้ถูกใช้แล้ว';
                } else {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare('INSERT INTO users (username, email, password_hash) VALUES (?, ?, ?)');
                    $stmt->execute([$signup_username ?: null, $signup_email, $hash]);
                    $id = $pdo->lastInsertId();

                    $_SESSION['user'] = [
                        'id' => $id,
                        'email' => $signup_email,
                        'username' => $signup_username
                    ];
                    header('Location: index.php');
                    exit;
                }
            } catch (Exception $e) {
                $signup_errors[] = 'เกิดข้อผิดพลาดในการบันทึกข้อมูล';
            }
        }
    }
}
?>
<!doctype html>
<html lang="th">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Sign in & Sign Up — Anime Finder</title>

    <!-- Font Awesome (สำหรับไอคอน) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

    <!-- ใช้ styles2.css (ปรับ path ถ้าไฟล์อยู่ในโฟลเดอร์อื่น) -->
    <link rel="stylesheet" href="responsive/styles2.css">
    <style>
        /* กรณีอยากให้ข้อความ error เด่นขึ้น — ถ้า styles2.css ไม่มี */
        .err { color: #b00020; margin: 6px 0; }
        .err-box { background:#fff6f6; border:1px solid #f1c2c2; padding:8px; border-radius:6px; margin-bottom:10px; }
        .small-note { text-align:center; font-size:0.95rem; color:#666; margin-top:10px; }
    </style>
</head>
<body>
    <div class="container">
        <div class="forms_container">
            <div class="signin_signup">

        <!-- SIGN IN FORM -->
        <form action="auth.php" method="post" class="sign_in_form" novalidate>
            <input type="hidden" name="form_type" value="signin">
            <h2 class="title">Sign in</h2>

            <?php if (!empty($signin_errors)): ?>
                <div class="err-box">
                    <?php foreach ($signin_errors as $e): ?>
                        <p class="err"><?= h($e) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="input_field">
                <i class="fas fa-user"></i>
                <input type="text" name="email" placeholder="Username or Email" value="<?= h($signin_email) ?>">
            </div>

            <div class="input_field">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Password">
            </div>

            <input type="submit" value="Login" class="btn solid">

            <p class="social-text">Or Sign in with social platforms</p>
            <div class="social-media">
                <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                <a href="#" class="social-icon"><i class="fab fa-google"></i></a>
                <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
            </div>
        </form>

        <!-- SIGN UP FORM -->
        <form action="auth.php" method="post" class="sign_up_form" novalidate>
            <input type="hidden" name="form_type" value="signup">
            <h2 class="title">Sign up</h2>

            <?php if (!empty($signup_errors)): ?>
                <div class="err-box">
                    <?php foreach ($signup_errors as $e): ?>
                        <p class="err"><?= h($e) ?></p>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <div class="input_field">
                <i class="fas fa-user"></i>
                <input type="text" name="username" placeholder="Username" value="<?= h($signup_username) ?>">
            </div>

            <div class="input_field">
                <i class="fas fa-envelope"></i>
                <input type="text" name="email" placeholder="Email" value="<?= h($signup_email) ?>">
            </div>

            <div class="input_field">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Password">
            </div>

            <input type="submit" value="Sign up" class="btn solid">

            <p class="social-text">Or Sign up with social platforms</p>
            <div class="social-media">
                <a href="#" class="social-icon"><i class="fab fa-facebook-f"></i></a>
                <a href="#" class="social-icon"><i class="fab fa-twitter"></i></a>
                <a href="#" class="social-icon"><i class="fab fa-google"></i></a>
                <a href="#" class="social-icon"><i class="fab fa-linkedin-in"></i></a>
            </div>
        </form>

        </div>
    </div>

    <!-- panels (keep original layout/images) -->
    <div class="panels-container">
        <div class="panel left-panel">
            <div class="content">
                <h3>New here?</h3>
                    <p>Let's search Anime Together!!, by the way you want some popcorn from me bro??</p>
            <button class="btn transparent" id="sign_up_btn">Sign up</button>
            </div>
            <img src="./undraw_horror-movie_9020.svg" class="image" alt="">
        </div>

        <div class="panel right-panel">
            <div class="content">
            <h3>One of us?</h3>
            <p>Let's join our Otaku Group for free!!, just enter the Sign up Form</p>
            <button class="btn transparent" id="sign_in_btn">Sign in</button>
            </div>
            <img src="./undraw_movie-night_pkvp.svg" class="image" alt="">
        </div>
    </div>
    </div>

    <!-- App JS (swtich panels). Path: adjust if app.js is in another folder -->
    <script src="app.js"></script>

    <!-- Auto-open signup/signin by URL param e.g. auth.php?show=signup -->
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const params = new URLSearchParams(location.search);
            const mode = params.get('show'); // 'signup' or 'signin'
            const container = document.querySelector('.container');
            if (!container) return;
            if (mode === 'signup') container.classList.add('sign-up-mode');
            if (mode === 'signin') container.classList.remove('sign-up-mode');

      // If server returned errors for signup, force sign-up panel
        <?php if (!empty($signup_errors)): ?>
            container.classList.add('sign-up-mode');
        <?php endif; ?>

      // If server returned signin errors, force sign-in panel
        <?php if (!empty($signin_errors)): ?>
            container.classList.remove('sign-up-mode');
        <?php endif; ?>
        });
    </script>
</body>
</html>
