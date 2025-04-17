<?php
require_once __DIR__ . '/../src/lang_helper.php';
require_once __DIR__ . '/../src/db.php';
include 'navbar.php';

$user_id = $_SESSION['user_id'] ?? null;

function is_cursed($username) {
    $cursed = json_decode(file_get_contents(__DIR__ . '/../data/cursed_users.json'), true);
    return in_array($username, $cursed, true);
}

// Mark all unread blogs as read (as before)
if ($user_id) {
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

// Handle new comment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['blog_id'], $_POST['comment_content'])) {
    if (!$user_id) {
        echo '<div style="color:red;">' . t('login_to_comment') . '</div>';
    } else {
        $blog_id = (int)$_POST['blog_id'];
        $content = $_POST['comment_content'];
        $stmt = $pdo->prepare("INSERT INTO comments (blog_id, user_id, content) VALUES (?, ?, ?)");
        $stmt->execute([$blog_id, $user_id, $content]);
        echo '<div style="color:green;">' . t('comment_posted') . '</div>';
    }
}

$stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$current_username = $stmt->fetchColumn();

$is_cursed = is_cursed($current_username);
$duplication_min = $is_cursed ? 4 : 1;
$duplication_max = $is_cursed ? 20 : 2;

// Show blogs
$stmt = $pdo->query("SELECT * FROM blogs ORDER BY created_at DESC");
$blogs = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($blogs as $row) {
    $repeat = rand($duplication_min, $duplication_max);
    for ($i = 0; $i < $repeat; $i++) {
        echo "<h2>" . maybe_reverse($row['title']) . "</h2>";
        echo "<div>" . $row['content'] . "</div>";
        echo "<hr>";

        // Show comments
        $stmt2 = $pdo->prepare("SELECT c.*, u.username FROM comments c JOIN users u ON c.user_id = u.id WHERE c.blog_id = ? ORDER BY c.created_at ASC");
        $stmt2->execute([$row['id']]);
        $comments = $stmt2->fetchAll(PDO::FETCH_ASSOC);

        echo "<div><b>" . t('comments') . ":</b></div>";
        if ($comments) {
            echo '<ul style="list-style:none;padding-left:0;">';
            foreach ($comments as $c) {
                echo '<li style="margin-bottom:6px;">';
                echo '<b>' . maybe_reverse(htmlspecialchars($c['username'])) . '</b>: ';
                echo maybe_reverse(htmlspecialchars($c['content']));
                echo ' <small>(' . $c['created_at'] . ')</small>';
                echo '</li>';
            }
            echo '</ul>';
        } else {
            echo '<div style="color:#888;">' . t('no_comments') . '</div>';
        }

        // Add comment form
        if ($user_id) {
            ?>
            <form method="POST" style="margin-top:8px;">
                <input type="hidden" name="blog_id" value="<?= $row['id'] ?>">
                <input type="text" name="comment_content" placeholder="<?= t('comment_placeholder') ?>" style="width:70%;">
                <button type="submit"><?= t('add_comment') ?></button>
            </form>
            <?php
        } else {
            echo '<div style="color:#888;">' . t('login_to_comment') . '</div>';
        }
        echo "<hr>";
    }
}
?>
