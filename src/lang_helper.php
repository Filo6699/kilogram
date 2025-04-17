<?php
session_start();

$langs = ['ru', 'kk', 'en', 'ja', 'vi'];
if (!isset($_SESSION['lang'])) {
    $_SESSION['lang'] = 'ru';
}

if (rand(1, 15) === 1) {
    $_SESSION['lang'] = 'kk';
}

if (isset($_GET['lang']) && in_array($_GET['lang'], $langs)) {
    $_SESSION['lang'] = $_GET['lang'];
    $url = strtok($_SERVER["REQUEST_URI"], '?');
    header("Location: $url");
    exit;
}

$lang = $_SESSION['lang'];
$L = require __DIR__ . '/lang.php';

function t($key) {
    global $L, $lang;
    return $L[$lang][$key] ?? $key;
}
