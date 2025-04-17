<?php
session_start();
require_once __DIR__ . '/../src/lang_helper.php';

$dir = __DIR__ . '/captcha_images/';
$files = array_values(array_filter(scandir($dir), function($f) use ($dir) {
    return is_file($dir . $f) && preg_match('/\.(png|jpg|jpeg|gif)$/i', $f);
}));

if (count($files) < 9) {
    echo t('not_enough_images');
    exit;
}

shuffle($files);
$chosen = array_slice($files, 0, 9);

$correct_key = $chosen[array_rand($chosen)];
$imagename = pathinfo($correct_key, PATHINFO_FILENAME);

// Format: replace _ with space, capitalize each word
$display_name = mb_convert_case(str_replace('_', ' ', $imagename), MB_CASE_TITLE, "UTF-8");

$_SESSION['captcha_image_answer'] = $correct_key;

echo "<div style='margin-bottom:8px;font-weight:bold;'>" . t('captcha_choose') . " <u>$display_name</u>:</div>";

echo "<div style='display:grid;grid-template-columns:repeat(3,60px);gap:8px;'>";
foreach ($chosen as $img) {
    echo "<label style='cursor:pointer;'>
        <input type='radio' name='captcha_image' value='$img' style='display:none;'>
        <img src='captcha_images/$img' width='60' height='60' style='border:2px solid #ccc;border-radius:6px;'>
    </label>";
}
echo "</div>";
?>
<style>
input[type="radio"][name="captcha_image"] + img {
    border: 2px solid #000000 !important;
}
input[type="radio"][name="captcha_image"]:checked + img {
    border: 2px solid #0f0 !important;
}
</style>
