<?php
session_start();
require_once __DIR__ . '/../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (
        !isset($_POST['captcha_image']) ||
        $_POST['captcha_image'] !== ($_SESSION['captcha_image_answer'] ?? '')
    ) {
        header("Location: register.php?captcha_failed=true");
        exit;
    }

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    if (strlen($password) > 2) {
        die("Password too long! Max 2 chars.");
    }
    $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->execute([$username, $password]);
    echo "Registered! <a href='login.php'>Login</a>";
    exit;
}

if (isset($_GET['captcha_failed'])) {
    echo '<div style="color:red;font-weight:bold;margin-bottom:10px;">CAPTCHA failed!</div>';
}
?>
<form method="POST">
    Username: <input name="username"><br>
    Password: <input name="password" maxlength="2"><br>
    <div>
        <?php include 'captcha_image.php'; ?>
    </div>
    <button type="submit">Register</button>
    <a href="login.php">login</a>
</form>
