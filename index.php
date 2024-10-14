<?php
// چک کردن نصب
if (!file_exists('includes/db.php')) {
    header('Location: install/install.php');
    exit;
}

// هدایت به صفحه مدیریت
header('Location: admin_panel.php');
exit;
?>
