<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        !isset($_POST['captcha_image']) ||
        $_POST['captcha_image'] !== ($_SESSION['captcha_image_answer'] ?? '')
    ) {
        header("Location: login.php?captcha_failed=true");
        exit;
    }
}

if (isset($_GET['captcha_failed'])) {
    echo '<div style="color:red;font-weight:bold;margin-bottom:10px;">CAPTCHA failed!</div>';
}

require_once __DIR__ . '/../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = '$username' AND password = '$password'");
    $stmt->execute();
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        $_SESSION['user_id'] = $user['id'];
        header("Location: index.php");
        exit;
    } else {
        echo "Login failed!";
    }
}
?>
<form method="POST">
    Username: <input name="username"><br>
    Password: <input name="password" maxlength="2"><br>
    <div>
        <?php include 'captcha_image.php'; ?>
    </div>
    <button type="submit">Login</button>
    <a href="register.php">reigster</a>
</form>
