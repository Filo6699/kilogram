<?php
session_start();
require_once __DIR__ . '/../src/lang_helper.php';
include 'navbar.php';
?>
<h1><?= maybe_reverse(t('welcome')) ?></h1>
