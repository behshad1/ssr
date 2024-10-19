<?php
session_start();
include 'config.php';  // نام کاربری و رمز عبور در این فایل ذخیره شده است

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // بررسی نام کاربری و رمز عبور
    if ($username === $panel_username && password_verify($password, $panel_password)) {
        $_SESSION['loggedin'] = true;
        header("Location: admin_panel.php");
        exit;
    } else {
        $error = "Invalid username or password.";
    }
}

// اگر کاربر وارد شده باشد، به پنل هدایت می‌شود
if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
    header("Location: admin_panel.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login to SSR Admin Panel</title>
</head>
<body>
    <h2>Login to SSR Admin Panel</h2>
    <form method="post" action="">
        <label for="username">Username:</label>
        <input type="text" id="username" name="username" required><br><br>
        <label for="password">Password:</label>
        <input type="password" id="password" name="password" required><br><br>
        <input type="submit" value="Login">
    </form>

    <?php if (isset($error)) { echo "<p style='color:red;'>$error</p>"; } ?>
</body>
</html>
