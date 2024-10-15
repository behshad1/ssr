<?php
session_start();
require_once 'functions.php'; // فایل توابع برای مدیریت اعتبارسنجی کاربر
include 'includes/db.php'; // برای اتصال به دیتابیس
// بررسی اگر کاربر قبلاً لاگین کرده باشد
if (isset($_SESSION['user_id'])) {
    header('Location: admin_panel.php');
    exit;
}

// بررسی درخواست لاگین
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // تابع بررسی اعتبار کاربر
    if (checkUserCredentials($username, $password)) {
        // در صورت صحیح بودن اعتبار، ذخیره اطلاعات در سشن
        $_SESSION['user_id'] = $username; // به‌طور مثال می‌توان نام کاربری را در سشن ذخیره کرد
        header('Location: admin_panel.php');
        exit;
    } else {
        $message = 'نام کاربری یا رمز عبور اشتباه است.';
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
    <h2>ورود به پنل مدیریت</h2>
    <?php if ($message): ?>
        <p style="color: red;"><?php echo $message; ?></p>
    <?php endif; ?>
    <form method="POST" action="login.php">
        <label for="username">نام کاربری:</label>
        <input type="text" id="username" name="username" required><br>

        <label for="password">رمز عبور:</label>
        <input type="password" id="password" name="password" required><br>

        <button type="submit">ورود</button>
    </form>
</body>
</html>
