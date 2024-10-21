<?php
require_once '/var/www/ssr-admin-panel/config.php';

// اتصال به دیتابیس با استفاده از PDO
try {
    $db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// تابع برای پاک کردن کدهای ANSI
function stripAnsiCodes($text) {
    return preg_replace('/\x1B\[[0-?]*[ -\/]*[@-~]/', '', $text);
}

// تابع برای تبدیل بایت به واحدهای خوانا (B, KB, MB, GB, TB)
function formatBytes($bytes) {
    if ($bytes >= 1099511627776) {
        $bytes = number_format($bytes / 1099511627776, 2) . ' TB';
    } elseif ($bytes >= 1073741824) {
        $bytes = number_format($bytes / 1073741824, 2) . ' GB';
    } elseif ($bytes >= 1048576) {
        $bytes = number_format($bytes / 1048576, 2) . ' MB';
    } elseif ($bytes >= 1024) {
        $bytes = number_format($bytes / 1024, 2) . ' KB';
    } else {
        $bytes = number_format($bytes, 2) . ' B';
    }

    return $bytes;
}

// تابع برای به‌روزرسانی اطلاعات ترافیک کاربران
function updateUsersTraffic($db) {
    // اجرای دستور برای دریافت اطلاعات کاربران
    $command = 'printf "5\n" | sudo -S /usr/local/bin/ssrrmu.sh';
    $output = shell_exec($command . ' 2>&1');

    // بررسی اینکه آیا دستور اجرا شده است یا خیر
    if ($output === null) {
        error_log("Command failed to execute");
        return "Failed to retrieve user list.";
    }

    // پاک کردن خروجی از کدهای ANSI
    $cleanOutput = stripAnsiCodes($output);

    // پردازش خروجی برای استخراج اطلاعات کاربران
    $lines = explode("\n", $cleanOutput);

    // نمایش عنوان جدول
    echo str_pad("Username", 20) . str_pad("Port", 10) . str_pad("Used Traffic", 20) . str_pad("Remaining Traffic", 20) . str_pad("Total Traffic", 20) . "\n";
    echo str_repeat("-", 100) . "\n";

    foreach ($lines as $line) {
        if (strpos($line, 'Username:') !== false) {
            preg_match('/Username: (.*?) Port: (\d+) Traffic usage \(used \+ remaining = total\): (.*?) \+ (.*?) = (.*)/', $line, $matches);
            if (count($matches) == 6) {
                $username = $matches[1];
                $port = $matches[2];
                $usedTraffic = convertTrafficToBytes($matches[3]);
                $remainingTraffic = convertTrafficToBytes($matches[4]);
                $totalTraffic = convertTrafficToBytes($matches[5]);

                // فرمت کردن خروجی جدول
                echo str_pad($username, 20) . str_pad($port, 10) . str_pad(formatBytes($usedTraffic), 20) . str_pad(formatBytes($remainingTraffic), 20) . str_pad(formatBytes($totalTraffic), 20) . "\n";

                // به‌روزرسانی دیتابیس
                $query = "UPDATE users SET used_traffic = :usedTraffic, remaining_traffic = :remainingTraffic, total_traffic = :totalTraffic WHERE port = :port";
                $stmt = $db->prepare($query);
                $stmt->execute([
                    'usedTraffic' => $usedTraffic,
                    'remainingTraffic' => $remainingTraffic,
                    'totalTraffic' => $totalTraffic,
                    'port' => $port
                ]);

                // اگر خطایی در به‌روزرسانی دیتابیس رخ دهد
                if (!$stmt) {
                    error_log("Failed to update traffic for user: " . $username . " on port: " . $port);
                }
            }
        }
    }
}

// تابع برای تبدیل ترافیک به بایت‌ها
function convertTrafficToBytes($traffic) {
    $units = array('B' => 1, 'KB' => 1024, 'MB' => 1048576, 'GB' => 1073741824, 'TB' => 1099511627776);

    // جدا کردن مقدار و واحد
    preg_match('/([\d.]+)\s*([KMGT]?B)/i', $traffic, $matches);
    if (count($matches) == 3) {
        $value = (float) $matches[1]; // مقدار عددی
        $unit = strtoupper($matches[2]); // واحد (B, KB, MB, ...)

        // تبدیل به بایت‌ها
        if (isset($units[$unit])) {
            return $value * $units[$unit];
        }
    }
    return 0; // اگر تطابقی پیدا نشد، مقدار صفر بازگردانده شود
}

// اجرای به‌روزرسانی
updateUsersTraffic($db);
