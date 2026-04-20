<?php
if(!isset($_SESSION)) {
    session_start();
}
$conn = mysqli_connect("localhost", "root", "", "to_inventory");
if(!$conn){
    die("Database connection Failed". mysqli_connect_error());
}

$sql = "SELECT role, name FROM users WHERE id = ?";
$check_stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($check_stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($check_stmt);
$result = mysqli_stmt_get_result($check_stmt);
$user_data = mysqli_fetch_assoc($result);

?>
<div class="universal-header">
    <div class ="user-info"><?php echo htmlspecialchars(strtoupper($user_data['role'])) . ' - ' . htmlspecialchars($user_data['name'] ?? $_SESSION['username']); ?></div>
    <?php if($user_data['role'] === 'ict'): ?>
    <a href="../ict/get-account.php" class="accounts-btn">Accounts</a>
    <?php endif; ?>
    <div class="user-menu">
        <img src="../images/mgb_logo.png" alt="" class="profile">
        <div class="dropdown-menu">
            <a href="../includes/profile-account.php">Profile Account</a>
            <a href="<?php echo isset($logout_path) ? $logout_path : '../logout.php'; ?>">Logout</a>
        </div>
    </div>
</div>
<style>
.universal-header{
    position:fixed;
    top:0;
    left:0;
    right:0;
    background:#f5f5f5;
    color:#fff;
    padding:5px 30px;
    display:flex;
    justify-content:space-between;
    align-items:center;
    z-index:999;
    box-shadow:0 2px 5px rgba(0,0,0,0.1);
}
.user-menu{
    position:relative;
    right: 30px;
    margin-left:auto;
    cursor:pointer;
}
.profile{
    width:45px;
    height:45px;
    border-radius:50%;
    object-fit:cover;
}
.dropdown-menu{
    display:none;
    position:absolute;
    right:0;
    top:calc(100);
    background:#fff;
    min-width:150px;
    box-shadow:0 4px 8px rgba(0,0,0,0.2);
    border-radius:4px;
}
.dropdown-menu a{
    display:block;
    padding:12px 20px;
    text-decoration:none;
    color:#333;
}
.dropdown-menu a:hover{
    background:#f5f5f5;
}
.user-menu:hover .dropdown-menu{
    display:block;
}
body{
    padding-top:70px;
    font-family: Arial, Helvetica, sans-serif;
}
.user-info{ 
    font-weight: bold;
    color: black;
    font-size: 20px;
}
.accounts-btn{
    background:#4CAF50;
    color:white;
    padding:10px 20px;
    border-radius:5px;
    text-decoration:none;
    font-weight:bold;
    transition:background 0.3s;
    position: absolute;
    right: 250px;
}
.accounts-btn:hover{
    background:#45a049;
}
</style>
