<?php
session_start();
require_once __DIR__ . '/../src/lang_helper.php';
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id || $user_id && rand(1, 50) === 1) {
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

function maybe_glitch($text) {
    if (rand(0, 12) === 0) {
        return '<span class="kg-glitch" data-text="' . htmlspecialchars($text) . '">' . htmlspecialchars($text) . '</span>';
    }
    return htmlspecialchars($text);
}

function maybe_reverse($text) {
    return maybe_glitch((rand(0, 7) > 6) ? mb_strrev($text) : $text);
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
    '<a href="https://www.youtube.com/watch?v=dQw4w9WgXcQ">' . maybe_reverse(t('logout')) . '</a>',
    '<a href="login.php">' . maybe_reverse(t(key: 'logout')) . '</a>',
    '<a href="login.php">' . maybe_reverse(t('logout')) . '</a>',
    '<a href="index.php">' . maybe_reverse(t('home')) . '</a>',
    '<a href="user_search.php">' . maybe_reverse(t('search_users')) . '</a>',
    '<a href="send_message.php">' . maybe_reverse(t('send_message')) . ($msg_count ? ' <b>(' . maybe_reverse((string)$msg_count) . ')</b>' : '') . '</a>',
    '<a href="chats.php">' . maybe_reverse(t('chats')) . ($msg_count ? ' <b>(' . maybe_reverse((string)$msg_count) . ')</b>' : '') . '</a>',
    '<a href="blog.php">' . maybe_reverse(t('write_blog')) . '</a>',
    '<a href="view_blogs.php">' . maybe_reverse(t('view_blogs')) . ($blog_count ? ' <b>(' . maybe_reverse((string)$blog_count) . ')</b>' : '') . '</a>',
    '<a href="?lang=ru">RU</a> <a href="?lang=kk">KK</a> <a href="?lang=en">EN</a> <a href="?lang=ja">JA</a> <a href="?lang=vi">VI</a>',
];
shuffle($links);

?>
<button type="button"><?= maybe_reverse(t('go_back')) ?></button>
<button type="button"><?= maybe_reverse(t('go_forward')) ?></button>
<button type="button"><?= maybe_reverse(t('reload')) ?></button>

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
        <div><?= t('loading') ?></div>
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
    echo ' | <span>' . maybe_reverse(t('logged_in_as') . $user_id) . '</span>';
}
echo '</nav>';
?>

<style>
/* Glitch effect for text */
.kg-glitch {
    position: relative;
    color: #fff;
    animation: glitch-anim 5s infinite linear alternate-reverse;
}
.kg-glitch::before, .kg-glitch::after {
    content: attr(data-text);
    position: absolute;
    left: 0; top: 0;
    width: 100%; overflow: hidden;
}
.kg-glitch::before {
    color: #f0f;
    z-index: 1;
    animation: glitch-anim-2 5s infinite linear alternate-reverse;
}
.kg-glitch::after {
    color: #0ff;
    z-index: 2;
    animation: glitch-anim-3 5s infinite linear alternate-reverse;
}
@keyframes glitch-anim {
    0% { text-shadow: 1px 0 red, -1px 0 blue; }
    20% { text-shadow: -1px 0 red, 1px 0 blue; }
    40% { text-shadow: 1px 1px red, -1px -1px blue; }
    60% { text-shadow: -1px 1px red, 1px -1px blue; }
    80% { text-shadow: 1px -1px red, -1px 1px blue; }
    100% { text-shadow: 0 0 red, 0 0 blue; }
}
@keyframes glitch-anim-2 {
    0% { left: 1px; }
    20% { left: -1px; }
    40% { left: 1px; }
    60% { left: -1px; }
    80% { left: 1px; }
    100% { left: 0; }
}
@keyframes glitch-anim-3 {
    0% { left: -1px; }
    20% { left: 1px; }
    40% { left: -1px; }
    60% { left: 1px; }
    80% { left: -1px; }
    100% { left: 0; }
}

.kg-glitch-img {
    animation: glitch-img-anim 4.7s infinite linear alternate-reverse;
    position: relative;
}
@keyframes glitch-img-anim {
    0% { filter: hue-rotate(0deg) blur(20px); left: 0; top: 0; }
    20% { filter: hue-rotate(20deg) blur(20px); left: 1px; top: -1px; }
    40% { filter: hue-rotate(-20deg) blur(20px); left: -1px; top: 1px; }
    60% { filter: hue-rotate(10deg) blur(20px); left: 1px; top: -1px; }
    80% { filter: hue-rotate(-10deg) blur(20px); left: -1px; top: 1px; }
    100% { filter: hue-rotate(0deg) blur(20px); left: 0; top: 0; }
}


body, input, button, textarea {
    font-size: 1em;
    max-width: 100vw;
    box-sizing: border-box;
}
form, nav, .kg-glitch, .kg-glitch-img {
    max-width: 100vw;
    word-break: break-word;
}
nav {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
    font-size: 1em;
}
form input, form textarea, form button {
    width: 100%;
    max-width: 400px;
    margin-bottom: 8px;
}
@media (max-width: 600px) {
    nav { font-size: 0.95em; }
    h1, h2, h3 { font-size: 1.2em; }
    form input, form textarea, form button { font-size: 1em; }
}
</style>

<meta name="viewport" content="width=device-width, initial-scale=1">
