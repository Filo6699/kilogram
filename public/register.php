<?php
session_start();
require_once __DIR__ . '/../src/lang_helper.php';
require_once __DIR__ . '/../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        !isset($_POST['captcha_image']) ||
        $_POST['captcha_image'] !== ($_SESSION['captcha_image_answer'] ?? '')
    ) {
        header("Location: register.php?captcha_failed=true");
        exit;
    }

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    if (strlen($password) > 2) {
        die(t('password_too_long'));
    }
    $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->execute([$username, $password]);
    echo t('register_success');
    exit;
}

if (isset($_GET['captcha_failed'])) {
    echo '<div style="color:red;font-weight:bold;margin-bottom:10px;">' . t('captcha_failed') . '</div>';
}
?>
<a href="?lang=ru">RU</a> <a href="?lang=kk">KK</a> <a href="?lang=en">EN</a> <a href="?lang=ja">JA</a> <a href="?lang=vi">VI</a>
<form method="POST">
    <?= t('username') ?>: <input name="username"><br>
    <?= t('password') ?>: <input name="password" maxlength="2"><br>
    <div>
        <?php include 'captcha_image.php'; ?>
    </div>
    <button type="submit"><?= t('register') ?></button>
    <a href="login.php"><?= t('login') ?></a>
</form>
