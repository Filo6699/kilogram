<?php
session_start();
require_once __DIR__ . '/../src/db.php';

include 'navbar.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        !isset($_POST['captcha_image']) ||
        $_POST['captcha_image'] !== ($_SESSION['captcha_image_answer'] ?? '')
    ) {
        header("Location: send_message.php?captcha_failed=true");
        exit;
    }
}

if (isset($_GET['captcha_failed'])) {
    echo '<div style="color:red;font-weight:bold;margin-bottom:10px;">CAPTCHA failed!</div>';
}

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) die("Login first!");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $to_username = $_POST['to_username'] ?? '';
    $content = $_POST['content'] ?? '';

    // Find recipient by username
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$to_username]);
    $to_user = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$to_user) {
        die("Recipient not found!");
    }
    $to = $to_user['id'];

    if ($to === $user_id) {
        die('u can\\\' send to urself');
    }

    // Count messages sent today
    $today = date('Y-m-d');
    $stmt = $pdo->prepare(
        "SELECT COUNT(*) FROM messages WHERE sender_id = ? AND DATE(sent_at) = ?"
    );
    $stmt->execute([$user_id, $today]);
    $count = $stmt->fetchColumn();

    if ($count >= 25) {
        die("Message limit reached for today!");
    }

    $stmt = $pdo->prepare(
        "INSERT INTO messages (sender_id, receiver_id, content, sent_at) VALUES (?, ?, ?, NOW())"
    );
    $stmt->execute([$user_id, $to, $content]);

    echo "Message sent!";
}
?>
<form method="POST">
    To (username): <input name="to_username"><br>
    Message: <textarea name="content"></textarea><br>
    <div>
        <?php include 'captcha_image.php'; ?>
    </div>
    <button type="submit">Send</button>
</form>
<p><a href="user_search.php">Search for users</a></p>
