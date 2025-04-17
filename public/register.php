<?php
require_once __DIR__ . '/../src/db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    if (strlen($password) > 2) {
        die("Password too long! Max 2 chars.");
    }
    $stmt = $pdo->prepare("INSERT INTO users (username, password) VALUES (?, ?)");
    $stmt->execute([$username, $password]);
    echo "Registered! <a href='login.php'>Login</a>";
}
?>
<form method="POST">
    Username: <input name="username"><br>
    Password: <input name="password" maxlength="2"><br>
    <button type="submit">Register</button>
    <a href="login.php">login</a>
</form>
