<?php
require_once 'config.php';

// تابع برای پاک کردن کدهای ANSI
function stripAnsiCodes($text) {
    return preg_replace('/\x1B\[[0-?9;]*[mG]/', '', $text);
}

// تابع برای مشاهده اطلاعات کاربر بر اساس پورت
function viewUser($port) {
    if (empty($port) || !is_numeric($port)) {
        return "Invalid port provided.";
    }

    $command = 'printf "5\n' . $port . '" | sudo -S /usr/local/bin/ssrrmu.sh';
    error_log("Executing View User command: " . $command);

    $output = shell_exec($command . ' 2>&1');

    if ($output === null) {
        error_log("Command failed to execute");
        return "Failed to retrieve user information.";
    } else {
        error_log("View User output: " . $output);
    }

    if (strpos($output, 'Did not find the user') !== false) {
        return "User not found. Please check the port number.";
    }

    $cleanOutput = stripAnsiCodes($output);

    $userInfoStart = strpos($cleanOutput, "User [") !== false ? strpos($cleanOutput, "User [") : 0;
    $userInfoEnd = strpos($cleanOutput, "Note:") !== false ? strpos($cleanOutput, "Note:") : strlen($cleanOutput);
    
    $userInfo = substr($cleanOutput, $userInfoStart, $userInfoEnd - $userInfoStart);
    
    return "<pre>$userInfo</pre>";
}

// تابع برای لیست کاربران
function listUsers() {
    $command = 'printf "5\n" | sudo -S /usr/local/bin/ssrrmu.sh';
    $output = shell_exec($command . ' 2>&1');

    if ($output === null) {
        error_log("Command failed to execute");
        return "Failed to retrieve user list.";
    }

    $cleanOutput = stripAnsiCodes($output);

    $lines = explode("\n", $cleanOutput);
    $userCount = substr_count($cleanOutput, 'Username:');
    
    $userList = "=== The total number of users: $userCount ===\n\n";
    $userList .= str_pad("Username", 20) . str_pad("Port", 10) . str_pad("Used Traffic", 20) . str_pad("Remaining Traffic", 20) . str_pad("Total Traffic", 20) . "\n";
    $userList .= str_repeat("-", 100) . "\n";

    foreach ($lines as $line) {
        if (strpos($line, 'Username:') !== false) {
            preg_match('/Username: (.*?) Port: (\d+) Traffic usage \(used \+ remaining = total\): (.*?) \+ (.*?) = (.*)/', $line, $matches);
            if (count($matches) == 6) {
                $username = $matches[1];
                $port = $matches[2];
                $usedTraffic = $matches[3];
                $remainingTraffic = $matches[4];
                $totalTraffic = $matches[5];

                $userList .= str_pad($username, 20) . str_pad($port, 10) . str_pad($usedTraffic, 20) . str_pad($remainingTraffic, 20) . str_pad($totalTraffic, 20) . "\n";
                
                
                // ذخیره‌سازی اطلاعات در آرایه
                $usersData[] = [
                    'username' => $username,
                    'port' => $port,
                    'used_traffic' => $usedTraffic,
                    'remaining_traffic' => $remainingTraffic,
                    'total_traffic' => $totalTraffic,
                ];
            }
        }
    }

    return $userList;
}

// تابع برای نمایش اطلاعات کاربر پیش‌فرض با پورت 1000
function showDefaultUser() {
    $defaultPort = 1000;
    return viewUser($defaultPort);
}




// تابع برای تبدیل لینک SSR
function convertSSRLink($link) {
    // حذف پیشوند 'ssr://' در صورتی که وجود داشته باشد
    if (strpos($link, 'ssr://') === 0) {
        $link = substr($link, 6); // حذف 'ssr://'
    }

    // دیکد کردن لینک از Base64 (فقط قسمت اصلی)
    $decoded_link = base64_decode(str_replace(array('-', '_'), array('+', '/'), $link));

    // بررسی دیکد شدن لینک
    if ($decoded_link === false) {
        error_log("Error decoding Base64 link.");
        return false;  // در صورت خطا، تابع false برمی‌گرداند
    }

    // بررسی پارامترهای اضافی (اگر در لینک وجود دارند)
    if (strpos($decoded_link, '?') !== false) {
        $parts = explode('?', $decoded_link);
        $main_part = $parts[0]; // بخش اصلی لینک
        parse_str($parts[1], $params); // پارامترهای اضافی از لینک
    } else {
        $main_part = $decoded_link;
        $params = [];
    }

    // افزودن پارامترهای جدید به لینک (اگر نیاز است پارامترهایی را اضافه کنیم)
    $params['obfsparam'] = isset($params['obfsparam']) ? $params['obfsparam'] : '';
    $params['protoparam'] = isset($params['protoparam']) ? $params['protoparam'] : '';
    $params['remarks'] = isset($params['remarks']) ? $params['remarks'] : '';
    $params['group'] = isset($params['group']) ? $params['group'] : '';

    // ایجاد لینک نهایی
    $modified_link = $main_part . "/?" . http_build_query($params);

    // انکد کردن لینک به Base64
    $encoded_link = base64_encode($modified_link);

    // جایگزینی کاراکترهای Base64 با فرمت URL-safe
    $encoded_link = str_replace(array('+', '/'), array('-', '_'), rtrim($encoded_link, '='));

    // بازگشت لینک نهایی با 'ssr://'
    return 'ssr://' . $encoded_link;
}

// تابع برای افزودن کاربر
function addUser($username, $port, $traffic) {
    // پاک کردن فضاهای خالی احتمالی از ورودی‌ها
    $username = trim($username);
    $port = trim($port);  // مطمئن می‌شویم که پورت تنها یک عدد است
    $traffic = trim($traffic);

    // تنظیم دستور برای اجرای اسکریپت با استفاده از echo -e
    $command = 'printf "7\n1\n' 
               . $username . '\n' 
               . $port . '\n\n'  // پسورد خالی
               . '7\n5\n2\n\n10\n\n\n'  // ادامه مراحل
               . $traffic . '\n\nn\n" | sudo /usr/local/bin/ssrrmu.sh';

    // ثبت دستور در لاگ برای دیباگ
    error_log("Executing Add User command: " . $command);

    // اجرای دستور
    $output = shell_exec($command . ' 2>&1');

    // بررسی اینکه آیا دستور اجرا شده است یا خیر
    if ($output === null) {
        error_log("Command failed to execute");
        return "Failed to add user.";
    } else {
        error_log("Add User output: " . $output);
    }

    // بازگشت خروجی پاک شده از کدهای ANSI
    $cleanOutput = stripAnsiCodes($output);
    
    // بررسی موفقیت‌آمیز بودن ایجاد کاربر در سیستم
    if (strpos($cleanOutput, 'User added successfully') !== false) {
        // استخراج لینک SSR از خروجی
        preg_match('/ssr:\/\/[^\s]+/', $cleanOutput, $matches);
        $ssrLink = isset($matches[0]) ? $matches[0] : '';

        if (empty($ssrLink)) {
            return "Failed to extract SSR link.";
        }

        // تبدیل لینک SSR
        $convertedLink = convertSSRLink($ssrLink);
        if ($convertedLink === false) {
            return "Failed to convert SSR link.";
        }

        // حالا اطلاعات کاربر و لینک را به دیتابیس اضافه می‌کنیم
       try {
    // اتصال به دیتابیس با استفاده از اطلاعات از config.php
            $db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
            $stmt = $db->prepare("INSERT INTO users (username, port, traffic, ssr_link, converted_link) VALUES (:username, :port, :traffic, :ssr_link, :converted_link)");
            $stmt->bindParam(':username', $username);
            $stmt->bindParam(':port', $port);
            $stmt->bindParam(':traffic', $traffic);
            $stmt->bindParam(':ssr_link', $ssrLink);
            $stmt->bindParam(':converted_link', $convertedLink); // افزودن bindParam برای converted_link

            if ($stmt->execute()) {
                return "User added successfully and SSR link stored in the database.";
            } else {
                return "Failed to add user to database.";
            }
        } catch (PDOException $e) {
            error_log("Database error: " . $e->getMessage());
            return "Database error: " . $e->getMessage();
        }
    } else {
        return "Failed to add user in the system: " . $cleanOutput;
    }
}

// تابع برای پاک کردن کدهای ANSI از خروجی
if (!function_exists('stripAnsiCodes')) {
    function stripAnsiCodes($text) {
        return preg_replace('/\x1B\[[0-9;]*[a-zA-Z]/', '', $text);
    }
}



// تابع برای حذف کاربر
function deleteUser($port) {
    if (empty($port) || !is_numeric($port)) {
        return "Invalid port provided.";
    }

    // تنظیم دستور برای حذف کاربر از سیستم
    $command = 'printf "7\n2\n' . $port . '\nn" | sudo -S /usr/local/bin/ssrrmu.sh';
    
    // ثبت دستور در لاگ برای دیباگ
    error_log("Executing Delete User command: " . $command);

    // اجرای دستور
    $output = shell_exec($command . ' 2>&1');

    // بررسی اینکه آیا دستور اجرا شده است یا خیر
    if ($output === null) {
        error_log("Command failed to execute");
        return "Failed to delete user.";
    } else {
        error_log("Delete User output: " . $output);
    }

    // بررسی موفقیت‌آمیز بودن حذف از سیستم
    if (strpos($output, '[information] 用户删除成功') !== false) {
        // در صورتی که حذف موفقیت‌آمیز باشد، کاربر را از دیتابیس حذف می‌کنیم
 
            $db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);

        // اجرای کوئری حذف
        $query = "DELETE FROM users WHERE port = $port";
        $result = $db->exec($query);

        if ($result) {
            return "User with port $port has been successfully deleted from both system and database.";
        } else {
            return "User with port $port was deleted from the system but failed to delete from the database.";
        }
    } else {
        // اگر موفق به حذف از سیستم نشد
        return "Failed to delete user from system. Output: " . stripAnsiCodes($output);
    }
}


function getUsersFromDatabase() {
    try {
        $db = new PDO('mysql:host=' . DB_HOST . ';dbname=' . DB_NAME, DB_USER, DB_PASS);
        $query = "SELECT * FROM users";
        $stmt = $db->query($query);
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        $userList = "=== Users from Database ===\n\n";
        $userList .= str_pad("ID", 10) . str_pad("Username", 20) . str_pad("Port", 10) . str_pad("Used Traffic", 20) . str_pad("Remaining Traffic", 20) . str_pad("Total Traffic", 20) . "\n";
        $userList .= str_repeat("-", 100) . "\n";
        
        foreach ($users as $user) {
            $userList .= str_pad((string)($user['id'] ?? 'N/A'), 10) 
                       . str_pad((string)($user['username'] ?? 'N/A'), 20) 
                       . str_pad((string)($user['port'] ?? 'N/A'), 10) 
                       . str_pad((string)($user['used_traffic'] ?? '0'), 20) 
                       . str_pad((string)($user['remaining_traffic'] ?? '0'), 20) 
                       . str_pad((string)($user['total_traffic'] ?? '0'), 20) . "\n";
        }
        
        return $userList;
    } catch (PDOException $e) {
        return "Database error: " . $e->getMessage();
    }
}

function install_SSR() {
    // دستور نصب اسکریپت (گزینه 1 به صورت دیفالت انتخاب می‌شود)
    $command = 'printf "1\n" | sudo -S /usr/local/bin/ssrrmu.sh';
    error_log("Executing Install command: " . $command);

    // اجرای دستور نصب
    $output = shell_exec($command . ' 2>&1');

    if ($output === null) {
        error_log("Command failed to execute during install");
        return "Installation failed.";
    } else {
        error_log("Install output: " . $output);
    }

    return "Installation completed successfully.\n$output";
}
function uninstall_SSR() {
    // دستور حذف اسکریپت (گزینه 3 برای حذف انتخاب می‌شود)
    $command = 'printf "3\ny" | sudo -S /usr/local/bin/ssrrmu.sh';
    error_log("Executing Uninstall command: " . $command);

    // اجرای دستور حذف
    $output = shell_exec($command . ' 2>&1');

    if ($output === null) {
        error_log("Command failed to execute during uninstall");
        return "Uninstallation failed.";
    } else {
        error_log("Uninstall output: " . $output);
    }

    return "Uninstallation completed successfully.\n$output";
}

