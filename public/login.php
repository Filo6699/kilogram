<?php
session_start();
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
    <button type="submit">Login</button>
    <a href="register.php">reigster</a>
</form>
