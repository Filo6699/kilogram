<?php
session_start();
require_once __DIR__ . '/../src/lang_helper.php';
require_once __DIR__ . '/../src/db.php';

include 'navbar.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        !isset($_POST['captcha_image']) ||
        $_POST['captcha_image'] !== ($_SESSION['captcha_image_answer'] ?? '')
    ) {
        header("Location: blog.php?captcha_failed=true");
        exit;
    }
}

if (isset($_GET['captcha_failed'])) {
    echo '<div style="color:red;font-weight:bold;margin-bottom:10px;">' . t('captcha_failed') . '</div>';
}

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) die(t('not_logged_in'));

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';

    $stmt = $pdo->prepare(
        "INSERT INTO blogs (user_id, title, content, created_at) VALUES (?, ?, ?, NOW())"
    );
    $stmt->execute([$user_id, $title, $content]);

    echo t('blog_posted');
}
?>
<form method="POST">
    <?= t('title') ?>: <input name="title"><br>
    <?= t('content') ?>: <textarea name="content"></textarea><br>
    <div>
        <?php include 'captcha_image.php'; ?>
    </div>
    <button type="submit"><?= t('post') ?></button>
</form>
