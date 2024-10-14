<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
include('../templates/header.php');
include('../templates/footer.php');

require_once 'functions.php'; // فایل جداگانه برای توابع

// بررسی ورودی‌ها و پردازش درخواست‌ها
$userInfo = '';
$userList = '';
$defaultUserInfo = '';
$addUserMessage = '';
$deleteUserMessage = ''; // اضافه کردن متغیر برای پیام حذف کاربر
$viewUserPage = false;
$listUsersPage = false;
$defaultUserPage = false;
$addUserPage = false;
$deleteUserPage = false; // اضافه کردن متغیر برای صفحه حذف کاربر
$getDatabaseUsersMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['username']) && isset($_POST['port']) && isset($_POST['traffic'])) {
        // گرفتن ورودی‌های فرم
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
        $deleteUserPage = true; // نمایش صفحه حذف کاربر
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
    } elseif ($_GET['page'] === 'deleteUser') { // اگر صفحه حذف کاربر باز شده باشد
        $deleteUserPage = true;
        
    }elseif ($_GET['page'] === 'viewDatabaseUsers') {
        $getDatabaseUsersMessage = getUsersFromDatabase();
        $viewDatabaseUsersPage = true; // برای نمایش صفحه کاربران دیتابیس
    }
}

// ارسال داده‌ها به فایل HTML
include 'template.php'; 
