<?php
session_start();
require_once __DIR__ . '/../src/lang_helper.php';
require_once __DIR__ . '/../src/db.php';

include 'navbar.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) die(t('not_logged_in'));

// Get all users you've chatted with
$stmt = $pdo->prepare("
    SELECT DISTINCT u.id, u.username
    FROM users u
    JOIN (
        SELECT receiver_id AS uid FROM messages WHERE sender_id = ?
        UNION
        SELECT sender_id AS uid FROM messages WHERE receiver_id = ?
    ) x ON u.id = x.uid
    WHERE u.id != ?
    ORDER BY u.username
");
$stmt->execute([$user_id, $user_id, $user_id]);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);

$chat_with = $_GET['with'] ?? null;
$messages = [];

if ($chat_with) {
    $stmt = $pdo->prepare("SELECT id FROM messages WHERE sender_id = ? AND receiver_id = ? AND id NOT IN (
        SELECT message_id FROM message_reads WHERE user_id = ?
    )");
    $stmt->execute([$chat_with, $user_id, $user_id]);
    $unread = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if ($unread) {
        $insert = $pdo->prepare("INSERT INTO message_reads (user_id, message_id) VALUES (?, ?)");
        foreach ($unread as $msg_id) {
            $insert->execute([$user_id, $msg_id]);
        }
    }

    $stmt = $pdo->prepare("
        SELECT m.*, u.username AS sender_name
        FROM messages m
        JOIN users u ON m.sender_id = u.id
        WHERE (sender_id = ? AND receiver_id = ?)
           OR (sender_id = ? AND receiver_id = ?)
        ORDER BY sent_at DESC
    ");
    $stmt->execute([$user_id, $chat_with, $chat_with, $user_id]);
    $messages = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Get chat partner's username
    $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
    $stmt->execute([$chat_with]);
    $partner = $stmt->fetchColumn();
}
?>
<h2><?= maybe_reverse(t('your_chats')) ?></h2>
<ul>
    <?php foreach ($users as $user): ?>
        <li>
            <a href="?with=<?= $user['id'] ?>">
                <?= maybe_reverse(htmlspecialchars($user['username'])) ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>

<?php if ($chat_with): ?>
    <h3><?= maybe_reverse(t('chat_with')) ?> <?= maybe_reverse(htmlspecialchars($partner)) ?></h3>
    <div style="border:1px solid #ccc; padding:10px; max-width:400px;">
        <?php foreach ($messages as $msg): ?>
            <div>
                <b><?= htmlspecialchars(maybe_reverse($msg['sender_name'])) ?>:</b>
                <?= maybe_reverse($msg['content']) ?> <!-- XSS possible! -->
                <small>(<?= maybe_reverse($msg['sent_at']) ?>)</small>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>
