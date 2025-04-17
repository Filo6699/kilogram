<?php
session_start();
require_once __DIR__ . '/../src/db.php';

include 'navbar.php';

$user_id = $_SESSION['user_id'] ?? null;
if (!$user_id) die("Login first!");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $title = $_POST['title'] ?? '';
    $content = $_POST['content'] ?? '';

    $stmt = $pdo->prepare(
        "INSERT INTO blogs (user_id, title, content, created_at) VALUES (?, ?, ?, NOW())"
    );
    $stmt->execute([$user_id, $title, $content]);

    echo "Blog posted!";
}
?>
<form method="POST">
    Title: <input name="title"><br>
    Content: <textarea name="content"></textarea><br>
    <button type="submit">Post</button>
</form>
