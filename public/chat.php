<?php
require_once __DIR__ . '/../src/lang_helper.php';
require_once __DIR__ . '/../src/db.php';
include 'navbar.php';

function is_whitelisted($user_id) {
    $whitelist = json_decode(file_get_contents(__DIR__ . '/../data/whitelist.json'), true);
    return in_array($user_id, $whitelist, true);
}

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) die(t('not_logged_in'));

// Get current user info
$stmt = $pdo->prepare("SELECT username, messages_per_day FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user_row = $stmt->fetch(PDO::FETCH_ASSOC);
$current_username = $user_row['username'];
$messages_per_day = (int)($user_row['messages_per_day'] ?? 25);

$is_whitelisted = is_whitelisted($user_id);
$is_cursed = is_cursed($user_id);

$with_id = $_GET['with'] ?? '';
$stmt = $pdo->prepare("SELECT id, username FROM users WHERE id = ?");
$stmt->execute([$with_id]);
$with_user = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$with_user) die(t('recipient_not_found'));
$with_username = $with_user['username'];

if ($with_id == $user_id) die(t('no_send_to_self'));

// Handle message send
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (!$is_whitelisted) {
      if (
          !isset($_POST['captcha_image']) ||
          $_POST['captcha_image'] !== ($_SESSION['captcha_image_answer'] ?? '')
      ) {
          header("Location: chat.php?with=" . urlencode($with_username) . "&captcha_failed=true");
          exit;
      }
  }

  $content = $_POST['content'] ?? '';

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
  $stmt->execute([$user_id, $with_id, $content]);

  // DO NOT REDIRECT, just continue to render the page
  // header("Location: chat.php?with=" . urlencode($with_username));
  // exit;
}

// Mark all unread messages from this user as read
$stmt = $pdo->prepare("SELECT id FROM messages WHERE sender_id = ? AND receiver_id = ? AND id NOT IN (
    SELECT message_id FROM message_reads WHERE user_id = ?
)");
$stmt->execute([$with_id, $user_id, $user_id]);
$unread = $stmt->fetchAll(PDO::FETCH_COLUMN);
if ($unread) {
    $insert = $pdo->prepare("INSERT INTO message_reads (user_id, message_id) VALUES (?, ?)");
    foreach ($unread as $msg_id) {
        $insert->execute([$user_id, $msg_id]);
    }
}

// Fetch all messages between the two users
$stmt = $pdo->prepare("
    SELECT m.*, u.username AS sender_name
    FROM messages m
    JOIN users u ON m.sender_id = u.id
    WHERE (sender_id = ? AND receiver_id = ?)
       OR (sender_id = ? AND receiver_id = ?)
    ORDER BY sent_at DESC
");
$stmt->execute([$user_id, $with_id, $with_id, $user_id]);
$messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_GET['captcha_failed'])) {
    echo '<div style="color:red;font-weight:bold;margin-bottom:10px;">' . t('captcha_failed') . '</div>';
}
?>

<div style="
    max-width: 600px;
    width: 100vw;
    margin: 0 auto;
    padding: 8px;
    box-sizing: border-box;
">
    <h2 style="word-break: break-word;"><?= t('chat_with') ?> <?= htmlspecialchars($with_username) ?></h2>
    <div style="
        border:1px solid #ccc;
        border-radius:8px;
        padding:10px;
        background:#fafaff;
        min-height:200px;
        max-height:40vh;
        overflow-y:auto;
        width: 100%;
        box-sizing: border-box;
        word-break: break-word;
    ">
        <?php foreach ($messages as $msg): ?>
          <?php if ($is_cursed) include 'captcha_image.php'; ?>
            <div style="margin-bottom:8px;<?= $msg['sender_id'] == $user_id ? 'text-align:right;' : '' ?>">
                <span style="font-weight:bold;"><?= htmlspecialchars($msg['sender_name']) ?>:</span>
                <span><?= htmlspecialchars($msg['content']) ?></span>
                <small style="color:#888;"><?= $msg['sent_at'] ?></small>
            </div>
        <?php endforeach; ?>
    </div>
    <form method="POST" style="margin-top:10px; width:100%;">
        <textarea name="content"
            style="width:100%;max-width:100%;min-height:40px;box-sizing:border-box;resize:vertical;"
            placeholder="<?= t('message') ?>"></textarea><br>
        <?php if (!$is_whitelisted): ?>
            <div>
                <?php include 'captcha_image.php'; ?>
            </div>
        <?php endif; ?>
        <button type="submit" style="width:100%;max-width:100%;"><?= t('send') ?></button>
    </form>
</div>
