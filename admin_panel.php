<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// نیاز به توابع جداگانه برای عملکرد‌های مختلف
require_once 'functions.php'; 

// شروع جلسه (session) برای ذخیره اطلاعات کاربر
session_start();

// بررسی اینکه آیا کاربر لاگین کرده است یا نه
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header("Location: login.php");
    exit;
}


// متغیرهای مربوط به صفحه‌های مختلف
$userInfo = '';
$userList = '';
$defaultUserInfo = '';
$addUserMessage = '';
$deleteUserMessage = '';
$installMessage = '';  // برای نصب اسکریپت
$uninstallMessage = ''; // برای حذف اسکریپت
$viewUserPage = false;
$listUsersPage = false;
$defaultUserPage = false;
$addUserPage = false;
$deleteUserPage = false;
$viewDatabaseUsersPage = false;
$installPage = false;
$uninstallPage = false;
$getDatabaseUsersMessage = '';



// بررسی درخواست‌های POST برای عملکرد‌های مختلف
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['username']) && isset($_POST['port']) && isset($_POST['traffic'])) {
        // گرفتن ورودی‌های فرم افزودن کاربر
        $username = $_POST['username'];
        $port = intval($_POST['port']);
        $traffic = $_POST['traffic'];

        // فراخوانی تابع addUser برای افزودن کاربر
        $addUserMessage = addUser($username, $port, $traffic);
        $addUserPage = true;
    } elseif (isset($_POST['port'])) {
        // گرفتن ورودی برای مشاهده اطلاعات کاربر
        $port = intval($_POST['port']);
        $userInfo = viewUser($port);
        $viewUserPage = true;
    } elseif (isset($_POST['delete_port'])) {
        // گرفتن ورودی برای حذف کاربر
        $port = intval($_POST['delete_port']);
        $deleteUserMessage = deleteUser($port);
        $deleteUserPage = true;
    }
} elseif (isset($_GET['page'])) {
    if ($_GET['page'] === 'viewUser') {
        $viewUserPage = true;
    } elseif ($_GET['page'] === 'listUsers') {
        $userList = listUsers();
        $listUsersPage = true;
    } elseif ($_GET['page'] === 'showDefaultUser') {
        $defaultUserInfo = showDefaultUser();
        $defaultUserPage = true;
    } elseif ($_GET['page'] === 'addUser') {
        $addUserPage = true;
    } elseif ($_GET['page'] === 'deleteUser') {
        $deleteUserPage = true;
    } elseif ($_GET['page'] === 'viewDatabaseUsers') {
        $getDatabaseUsersMessage = getUsersFromDatabase();
        $viewDatabaseUsersPage = true;
    } elseif ($_GET['page'] === 'install') {
        // فراخوانی تابع نصب اسکریپت
        $installMessage = install_SSR();
        $installPage = true;
    } elseif ($_GET['page'] === 'uninstall') {
        // فراخوانی تابع حذف اسکریپت
        $uninstallMessage = uninstall_SSR();
        $uninstallPage = true;
    }
}

// ارسال داده‌ها به فایل HTML
include 'template.php';
?>
