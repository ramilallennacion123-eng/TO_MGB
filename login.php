<?php
session_start();

$conn = mysqli_connect("localhost", "root", "", "to_inventory");

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

$error_msg = "";

if (isset($_POST['login_btn'])) {
    
    $user_input = mysqli_real_escape_string($conn, $_POST['username']);
    $pass_input = $_POST['password'];

    $query = "SELECT * FROM users WHERE username = ?";
    $stmt  = mysqli_prepare($conn, $query);
    mysqli_stmt_bind_param($stmt, "s", $user_input);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    if ($user = mysqli_fetch_assoc($result)) {
        if (password_verify($pass_input, $user['password']) || $pass_input === $user['password']) {
            
            $_SESSION['user_id']  = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role']     = $user['role'];
            $_SESSION['planner']  = $user['planner'];

            if ($user['role'] == 'admin') {
                header("Location: admin/index.php");
                exit();
            } elseif ($user['role'] == 'chief') {
                header("Location: chief/division_officer.php");
                exit();
            } elseif ($user['role'] == 'ict') {
                header("Location: ict/index.php");
                exit();
            } elseif ($user['role'] == 'planner') {
                header("Location: planner/index.php");
            }else {
                $error_msg = "Your account has no assigned role. Contact support.";
            }

        } else {
            $error_msg = "Invalid username or password.";
        }
    } else {
        $error_msg = "User not found.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" type="text/css" href="style/login.css">
</head>
<body>

<div class="login-box">
    <div style="text-align: left; margin-bottom: 20px;">
        <a href="../TO_MGB/index.php" class="btn login-btn return-icon-btn" title="Return">&#8617;</a>
    </div>
    
    <h2>Login</h2>
    
    <?php if ($error_msg): ?>
        <div class="error"><?php echo $error_msg; ?></div>
    <?php endif; ?>

    <form action="login.php" method="POST">
        <input type="text" name="username" placeholder="Username" required>
        <input type="password" name="password" placeholder="Password" required>
        <button type="submit" name="login_btn">Login</button>
    </form>
</div>

</body>
</html>