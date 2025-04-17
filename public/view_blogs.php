<?php
require_once __DIR__ . '/../src/db.php';
include 'navbar.php';

$user_id = $_SESSION['user_id'] ?? null;

if ($user_id) {
    // Mark all unread blogs as read
    $stmt = $pdo->prepare("SELECT id FROM blogs WHERE id NOT IN (
        SELECT blog_id FROM blog_reads WHERE user_id = ?
    ) AND user_id != ?");
    $stmt->execute([$user_id, $user_id]);
    $unread = $stmt->fetchAll(PDO::FETCH_COLUMN);

    if ($unread) {
        $insert = $pdo->prepare("INSERT INTO blog_reads (user_id, blog_id) VALUES (?, ?)");
        foreach ($unread as $blog_id) {
            $insert->execute([$user_id, $blog_id]);
        }
    }
}

$stmt = $pdo->query("SELECT * FROM blogs ORDER BY created_at DESC");
$blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($blogs as $row) {
    $repeat = rand(1, 2);
    for ($i = 0; $i < $repeat; $i++) {
        echo "<h2>" . maybe_reverse($row['title']) . "</h2>";
        echo "<div>" . $row['content'] . "</div>";
        echo "<hr>";
    }
}
?>
