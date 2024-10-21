<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel</title>
    <link rel="stylesheet" type="text/css" href="styles.css">
</head>
<body>

<div class="flex-container">
    <nav>
        <h2>Admin Menu</h2>
        <ul>
            <li><a href="?page=viewUser">View User Information</a></li>
            <li><a href="?page=addUser">Add User</a></li>
            <li><a href="?page=listUsers">List Users</a></li>
            <li><a href="?page=viewDatabaseUsers">View Users from Database</a></li>
            <li><a href="?page=showDefaultUser">Show Default User</a></li>
            <li><a href="?page=deleteUser">Delete User</a></li>
            <li><a href="?page=install">Install SSR Script</a></li> <!-- نصب اسکریپت -->
            <li><a href="?page=uninstall">Uninstall SSR Script</a></li> <!-- حذف اسکریپت -->
                   <li><a href="logout.php">Logout</a></li>

 </ul>
    </nav>

    <div class="container">
        <?php if ($viewUserPage): ?>
            <?php if (!empty($userInfo)): ?>
                <h1>User Information</h1>
                <pre><?php echo htmlspecialchars($userInfo); ?></pre>
                <button class="back-button" onclick="window.history.back()">Back</button>
            <?php else: ?>
                <h1>View User Information</h1>
                <form method="post">
                    <label for="port">Enter User Port:</label>
                    <input type="text" id="port" name="port" required>
                    <input type="submit" value="View User">
                </form>
            <?php endif; ?>
        <?php elseif ($addUserPage): ?>
            <?php if (!empty($addUserMessage)): ?>
                <h1>Add User Result</h1>
                <pre><?php echo htmlspecialchars($addUserMessage); ?></pre>
                <button class="back-button" onclick="window.history.back()">Back</button>
            <?php else: ?>
                <h1>Add New User</h1>
                <form method="post">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                    
                    <label for="port">Port:</label>
                    <input type="number" id="port" name="port" required>
                    
                    <label for="traffic">Traffic (GB):</label>
                    <input type="text" id="traffic" name="traffic" required>

                    <input type="submit" value="Add User">
                </form>
            <?php endif; ?>
        <?php elseif ($listUsersPage): ?>
            <h1>User List</h1>
            <pre><?php echo htmlspecialchars($userList); ?></pre>
            <button class="back-button" onclick="window.history.back()">Back</button>
        <?php elseif ($defaultUserPage): ?>
            <h1>Default User Information (Port 1000)</h1>
            <pre><?php echo htmlspecialchars($defaultUserInfo); ?></pre>
            <button class="back-button" onclick="window.history.back()">Back</button>
        <?php elseif ($deleteUserPage): ?>
            <?php if (!empty($deleteUserMessage)): ?>
                <h1>Delete User Result</h1>
                <pre><?php echo htmlspecialchars($deleteUserMessage); ?></pre>
                
                <?php
                if (strpos($deleteUserMessage, '[information] 用户删除成功') !== false) {
                    echo '<script>alert("User deleted successfully!");</script>';
                } else {
                    echo '<script>alert("Failed to delete user. Please check the port and try again.");</script>';
                }
                ?>
                
                <button class="back-button" onclick="window.history.back()">Back</button>
            <?php else: ?>
                <h1>Delete User</h1>
                <form method="post">
                    <label for="delete_port">Enter User Port to Delete:</label>
                    <input type="text" id="delete_port" name="delete_port" required>
                    <input type="submit" value="Delete User">
                </form>
            <?php endif; ?>
        <?php elseif ($viewDatabaseUsersPage): ?>
            <h1>User List from Database</h1>
            <pre><?php echo htmlspecialchars($getDatabaseUsersMessage); ?></pre>
            <button class="back-button" onclick="window.history.back()">Back</button>
        <?php elseif ($installPage): ?>
            <h1>Install SSR Script</h1>
            <pre><?php echo htmlspecialchars($installMessage); ?></pre>
            <button class="back-button" onclick="window.history.back()">Back</button>
        <?php elseif ($uninstallPage): ?>
            <h1>Uninstall SSR Script</h1>
            <pre><?php echo htmlspecialchars($uninstallMessage); ?></pre>
            <button class="back-button" onclick="window.history.back()">Back</button>
        <?php else: ?>
            <h1>Welcome to Admin Panel</h1>
            <p>Please select an option from the menu.</p>
        <?php endif; ?>
    </div>
</div>

</body>
</html>
