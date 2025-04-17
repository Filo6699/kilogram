<?php
require_once __DIR__ . '/../src/lang_helper.php';
require_once __DIR__ . '/../src/db.php';

include 'navbar.php';

$search = $_GET['search'] ?? '';
$users = [];

$is_cursed = is_cursed(user_id: $_SESSION['user_id']);

if ($search === '') {
    $stmt = $pdo->query("SELECT id, username FROM users ORDER BY id ASC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
    $stmt = $pdo->prepare("SELECT id, username FROM users WHERE username ILIKE ? ORDER BY id ASC");
    $stmt->execute(['%' . $search . '%']);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<form method="GET">
    <?= maybe_reverse(t('search_users')) ?>: <input name="search" value="<?= htmlspecialchars($search) ?>">
    <button type="submit"><?= t('search') ?></button>
</form>
<?php if ($users): ?>
    <ul>
        <?php foreach ($users as $user): ?>
            <li><?= maybe_reverse(htmlspecialchars($user['username'])) ?> (ID: <?= $user['id'] ?>)</li>
        <?php endforeach; ?>
        <?php if ($is_cursed): ?>
            <div>
                <?php include 'captcha_image.php'; ?>
            </div>
        <?php endif; ?>
    </ul>
<?php endif; ?>
