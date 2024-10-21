<?php
session_start();
session_unset();  // حذف تمام متغیرهای session
session_destroy();  // پایان دادن به session

// هدایت به صفحه لاگین
header("Location: login.php");
exit;
?>
