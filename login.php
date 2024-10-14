<?php
session_start();
include 'includes/db.php'; // برای اتصال به دیتابیس

// بررسی ارسال فرم
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // بررسی نام کاربری در دیتابیس
    $stmt = $pdo->prepare('SELECT * FROM users WHERE username = ?');
    $stmt->execute([$username]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        // ذخیره اطلاعات کاربر در session
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['username'] = $user['username'];

        // هدایت به پنل مدیریت
        header('Location: admin_panel.php');
        exit;
    } else {
        $error = 'نام کاربری یا رمز عبور اشتباه است.';
    }
}
?>

<!DOCTYPE html>
<html lang="fa">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>ورود به پنل مدیریت</title>
</head>
<body>
    <h2>ورود به پنل مدیریت</h2>

    <?php if (isset($error)): ?>
        <p style="color: red;"><?php echo $error; ?></p>
    <?php endif; ?>

    <form method="POST" action="login.php">
        <label for="username">نام کاربری:</label>
        <input type="text" id="username" name="username" required>

        <label for="password">رمز عبور:</label>
        <input type="password" id="password" name="password" required>

        <button type="submit">ورود</button>
    </form>
</body>
</html>
