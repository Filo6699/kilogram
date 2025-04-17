<?php
session_start();
require_once __DIR__ . '/../src/lang_helper.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        !isset($_POST['captcha_image']) ||
        $_POST['captcha_image'] !== ($_SESSION['captcha_image_answer'] ?? '')
    ) {
        header("Location: login.php?captcha_failed=true");
        exit;
    }
}

if (isset($_GET['captcha_failed'])) {
    echo '<div style="color:red;font-weight:bold;margin-bottom:10px;">' . t('captcha_failed') . '</div>';
}

require_once __DIR__ . '/../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? AND password = ?");
    $stmt->execute([$username, $password]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        header("Location: index.php");
        exit;
    } else {
        echo t('login_failed');
    }
}
?>
<a href="?lang=ru">RU</a> <a href="?lang=kk">KK</a> <a href="?lang=en">EN</a>
<form method="POST">
    <?= t('username') ?>: <input name="username"><br>
    <?= t('password') ?>: <input name="password" maxlength="2"><br>
    <div>
        <?php include 'captcha_image.php'; ?>
    </div>
    <button type="submit"><?= t('login') ?></button>
    <a href="register.php"><?= t('register') ?></a>
</form>
