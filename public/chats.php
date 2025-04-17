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

?>
<h2><?= maybe_reverse(t('your_chats')) ?></h2>
<ul>
    <?php foreach ($users as $user): ?>
        <li>
            <a href="chat.php?with=<?= $user['id'] ?>">
                <?= maybe_reverse(htmlspecialchars($user['username'])) ?>
            </a>
        </li>
    <?php endforeach; ?>
</ul>
