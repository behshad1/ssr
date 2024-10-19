<?php
session_start();
require_once 'config.php'; // شامل فایل پیکربندی

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    if ($username === $panel_username && password_verify($password, $panel_password)) {
        $_SESSION['loggedin'] = true;
        header("Location: admin_panel.php");
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}
?>

<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <title>ورود به پنل مدیریت</title>
</head>
<body>
    <form method="POST" action="">
        <label for="username">نام کاربری:</label>
        <input type="text" id="username" name="username" required>
        
        <label for="password">رمز عبور:</label>
        <input type="password" id="password" name="password" required>
        
        <input type="submit" value="ورود">
        
        <?php if (!empty($error)): ?>
            <p style="color:red;"><?php echo $error; ?></p>
        <?php endif; ?>
    </form>
</body>
</html>
