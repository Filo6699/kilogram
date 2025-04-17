<?php
require_once __DIR__ . '/../src/db.php';

include 'navbar.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (
      !isset($_POST['captcha_image']) ||
      $_POST['captcha_image'] !== ($_SESSION['captcha_image_answer'] ?? '')
  ) {
      header("Location: user_search.php?captcha_failed=true");
      exit;
  }
}

if (isset($_GET['captcha_failed'])) {
  echo '<div style="color:red;font-weight:bold;margin-bottom:10px;">CAPTCHA failed!</div>';
}

$search = $_GET['search'] ?? '';
$users = [];

if ($search !== '') {
    $stmt = $pdo->prepare("SELECT id, username FROM users WHERE username ILIKE ?");
    $stmt->execute(['%' . $search . '%']);
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>
<form method="GET">
    Search users: <input name="search" value="<?= htmlspecialchars($search) ?>">
    <div>
        <?php include 'captcha_image.php'; ?>
    </div>
    <button type="submit">Search</button>
</form>
<?php if ($users): ?>
    <ul>
        <?php foreach ($users as $user): ?>
            <li><?= htmlspecialchars($user['username']) ?> (ID: <?= $user['id'] ?>)</li>
        <?php endforeach; ?>
    </ul>
<?php endif; ?>
