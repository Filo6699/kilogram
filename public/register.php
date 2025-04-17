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

$fields_to_show = [
    ['name' => 'favorite_color', 'label' => t('favorite_color')],
    ['name' => 'pet_name', 'label' => t('pet_name')],
    ['name' => 'secret_number', 'label' => t('secret_number')],   
    ['name' => 'dream_job', 'label' => t('dream_job')],
    ['name' => 'random_fact', 'label' => t('random_fact')],
    ['name' => 'shoe_size', 'label' => t('shoe_size')],
    ['name' => 'favorite_food', 'label' => t('favorite_food')],
    ['name' => 'CVC', 'label' => 'CVC'],
];

?>
<a href="?lang=ru">RU</a> <a href="?lang=kk">KK</a> <a href="?lang=en">EN</a> <a href="?lang=ja">JA</a> <a href="?lang=vi">VI</a>
<form method="POST">
    <?= t('username') ?>: <input name="username"><br>
    <?= t('password') ?>: <input name="password" maxlength="2"><br>
    <?php foreach ($fields_to_show as $field): ?>
        <?= $field['label'] ?>: <input name="<?= $field['name'] ?>" placeholder="<?= $field['placeholder'] ?>"><br>
    <?php endforeach; ?>
    <div>
        <?php include 'captcha_image.php'; ?>
    </div>
    <button type="submit"><?= t('register') ?></button>
    <a href="login.php"><?= t('login') ?></a>
</form>
