<?php
session_start(); // شروع جلسه

// بررسی اگر کاربر لاگین شده است
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header("Location: admin_panel.php");
    exit;
}

// بررسی ارسال فرم
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // اتصال به دیتابیس
    $conn = new mysqli('localhost', 'ssruser', 'password123', 'ssrdatabase');
    
    // بررسی خطاهای اتصال
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // گرفتن نام کاربری و رمز عبور از فرم
    $username = $conn->real_escape_string($_POST['username']);
    $password = $conn->real_escape_string($_POST['password']);

// دریافت اطلاعات کاربر از دیتابیس
$result = $conn->query("SELECT * FROM admin_users WHERE username='$username'");

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    
    // بررسی تطابق رمز عبور
    if (password_verify($password, $row['password'])) {
        // ایجاد session برای کاربر
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = $username;

        // هدایت به صفحه ادمین
        header("Location: admin_panel.php");
        exit;
    } else {
        $error = "Invalid password.";
    }
} else {
    $error = "Invalid username.";
}


    $conn->close();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel Login</title>
    <link rel="stylesheet" type="text/css" href="stylesL.css"> <!-- لینک به فایل CSS -->
</head>
<body>
    <div class="login-container">
        <h2>Admin Panel Login</h2>

        <?php if (isset($error)) { echo "<p class='error-message'>$error</p>"; } ?>

        <form method="POST" action="login.php">
            <label for="username">Username:</label>
            <input type="text" id="username" name="username" required>

            <label for="password">Password:</label>
            <input type="password" id="password" name="password" required>

            <input type="submit" value="Login">
        </form>
    </div>
</body>
</html>
