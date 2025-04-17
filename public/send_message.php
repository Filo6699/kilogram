<?php
session_start();
require_once __DIR__ . '/../src/lang_helper.php';
require_once __DIR__ . '/../src/db.php';

include 'navbar.php';

function is_whitelisted($username) {
    $whitelist = json_decode(file_get_contents(__DIR__ . '/../data/whitelist.json'), true);
    return in_array($username, $whitelist, true);
}

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) die(t('not_logged_in'));

// Fetch username and messages_per_day for whitelist and limit
$stmt = $pdo->prepare("SELECT username, messages_per_day FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user_row = $stmt->fetch(PDO::FETCH_ASSOC);
$current_username = $user_row['username'];
$messages_per_day = (int)($user_row['messages_per_day'] ?? 25);

$is_whitelisted = is_whitelisted($current_username);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$is_whitelisted) {
    if (
        !isset($_POST['captcha_image']) ||
        $_POST['captcha_image'] !== ($_SESSION['captcha_image_answer'] ?? '')
    ) {
        header("Location: send_message.php?captcha_failed=true");
        exit;
    }
}

if (isset($_GET['captcha_failed'])) {
    echo '<div style="color:red;font-weight:bold;margin-bottom:10px;">' . t('captcha_failed') . '</div>';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $to_username = $_POST['to_username'] ?? '';
    $content = $_POST['content'] ?? '';

    // Find recipient by username
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$to_username]);
    $to_user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$to_user) {
        die(t('recipient_not_found'));
    }
    $to = $to_user['id'];

    if ($to === $user_id) {
        die(t('no_send_to_self'));
    }

    // Count messages sent today
    $today = date('Y-m-d');
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM messages WHERE sender_id = ? AND DATE(sent_at) = ?"
    );
    $stmt->execute([$user_id, $today]);
    $count = $stmt->fetchColumn();

    if ($count >= $messages_per_day) {
        die(t('message_limit'));
    }

    $stmt = $pdo->prepare(
        "INSERT INTO messages (sender_id, receiver_id, content, sent_at) VALUES (?, ?, ?, NOW())"
    );
    $stmt->execute([$user_id, $to, $content]);

    echo t('message_sent');
}
?>

<form method="POST">
    <?= maybe_reverse(t('to_username')) ?>: <input name="to_username"><br>
    <?= maybe_reverse(t('message')) ?>: <textarea name="content"></textarea><br>
    <?php if (!$is_whitelisted): ?>
        <div>
            <?php include 'captcha_image.php'; ?>
        </div>
    <?php endif; ?>
    <button type="submit"><?= maybe_reverse(t('send')) ?></button>
</form>
<p><a href="user_search.php"><?= maybe_reverse(t('search_users')) ?></a></p>
