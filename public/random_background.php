<?php

$dir = __DIR__ . '/background_images/';
$files = array_values(array_filter(scandir($dir), function($f) use ($dir) {
    return is_file($dir . $f) && preg_match('/\.(png|webp|jpg|jpeg|gif)$/i', $f);
}));

if (count($files) === 0) return;

$num = rand(min: 5, max: 15);

// Generate random positions and sizes
echo '<style>#kg-bgimg { position:fixed; left:0; top:0; width:100vw; height:100vh; z-index:-1; pointer-events:none; overflow:hidden; }</style>';
echo '<div id="kg-bgimg">';
for ($i = 0; $i < $num; $i++) {
    $img = $files[array_rand($files)];
    $left = rand(-30, 90);
    $top = rand(-30, 90);
    $size = rand(300, 400);
    $opacity = rand(20, 90) / 100;
    $angle = rand(0, 360);
    echo '<img src="background_images/' . htmlspecialchars($img) . '" style="
        position:absolute;
        left:' . $left . '%;
        top:' . $top . '%;
        width:' . $size . 'px;
        opacity:' . $opacity . ';
        transform: rotate(' . $angle . 'deg);
        pointer-events:none;
        user-select:none;
        z-index:-15;
    ">';
}
echo '</div>';
?>
