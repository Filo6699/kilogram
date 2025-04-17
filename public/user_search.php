<?php
require_once __DIR__ . '/../src/lang_helper.php';
require_once __DIR__ . '/../src/db.php';

include 'navbar.php';

$search = $_GET['search'] ?? '';
$users = [];

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
    <?= t('search_users') ?>: <input name="search" value="<?= htmlspecialchars($search) ?>">
    <button type="submit"><?= t('search') ?></button>
</form>
<?php if ($users): ?>
    <ul>
        <?php foreach ($users as $user): ?>
            <li><?= htmlspecialchars($user['username']) ?> (ID: <?= $user['id'] ?>)</li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
