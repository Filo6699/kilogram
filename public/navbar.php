<?php
session_start();
$user_id = $_SESSION['user_id'] ?? null;

// Random logout: 1 in 25 chance
if (!$user_id || $user_id && rand(1, 25) === 1) {
    session_destroy();
    header("Location: login.php");
    exit;
}

// Random unreadable color scheme
$bg = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
$fg = sprintf('#%06X', mt_rand(0, 0xFFFFFF));
echo "<body style='background:$bg;color:$fg'>";

function mb_strrev($text, $encoding = 'UTF-8') {
    $length = mb_strlen($text, $encoding);
    $reversed = '';
    while ($length-- > 0) {
        $reversed .= mb_substr($text, $length, 1, $encoding);
    }
    return $reversed;
}

function maybe_reverse($text) {
    return (rand(0, 7) > 6) ? mb_strrev($text) : $text;
}

// Notification counts
$blog_count = 0;
$msg_count = 0;

if ($user_id) {
    require_once __DIR__ . '/../src/db.php';

    // Unread blogs
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM blogs WHERE id NOT IN (
        SELECT blog_id FROM blog_reads WHERE user_id = ?
    ) AND user_id != ?");
    $stmt->execute([$user_id, $user_id]);
    $blog_count = $stmt->fetchColumn();

    // Unread messages
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM messages WHERE receiver_id = ? AND id NOT IN (
        SELECT message_id FROM message_reads WHERE user_id = ?
    )");
    $stmt->execute([$user_id, $user_id]);
    $msg_count = $stmt->fetchColumn();
}

// Prepare links with notifications
$links = [
    '<a href="login.php">' . maybe_reverse('Logout') . '</a>',
    '<a href="login.php">' . maybe_reverse('Logout') . '</a>',
    '<a href="login.php">' . maybe_reverse('Logout') . '</a>',
    '<a href="index.php">' . maybe_reverse('Home') . '</a>',
    '<a href="user_search.php">' . maybe_reverse('Search Users') . '</a>',
    '<a href="send_message.php">' . maybe_reverse('Send Message') . '</a>',
    '<a href="chats.php">' . maybe_reverse('Chats') . ($msg_count ? ' <b>(' . maybe_reverse((string)$msg_count) . ')</b>' : '') . '</a>',
    '<a href="blog.php">' . maybe_reverse('Write Blog') . '</a>',
    '<a href="view_blogs.php">' . maybe_reverse('View Blogs') . ($blog_count ? ' <b>(' . maybe_reverse((string)$blog_count) . ')</b>' : '') . '</a>',
];
shuffle($links);

?>
<button type="button">gol back</button>
<button type="button">go forward</button>
<button type="button">reload</button>

<div id="kg-spinner" style="
    position:fixed;top:0;left:0;width:100vw;height:100vh;
    background:rgba(255,255,255,0.8);z-index:9999;
    display:flex;align-items:center;justify-content:center;
    font-size:2em;
">
    <div>
        <svg width="60" height="60" viewBox="0 0 100 100">
            <circle cx="50" cy="50" r="40" stroke="#888" stroke-width="8" fill="none" stroke-dasharray="188.4" stroke-dashoffset="0">
                <animateTransform attributeName="transform" type="rotate" from="0 50 50" to="360 50 50" dur="1s" repeatCount="indefinite"/>
            </circle>
        </svg>
        <div>Loading...</div>
    </div>
</div>
<script>
window.addEventListener('DOMContentLoaded', function() {
    if (Math.random() < 0.05) {
        return;
    }
    setTimeout(function() {
        document.getElementById('kg-spinner').style.display = 'none';
    }, Math.random() * 1000);
});
</script>

<?php

echo '<nav style="background:#eee;padding:10px;">' . implode(' | ', $links);
if ($user_id) {
    echo ' | <span>' . maybe_reverse("Logged in as #$user_id") . '</span>';
}
echo '</nav>';
?>
