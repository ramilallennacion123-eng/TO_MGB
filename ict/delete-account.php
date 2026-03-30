<?php 
session_start();
if($_SESSION['role'] !== 'ict'){
  die('Access Denied!');
}

$conn = mysqli_connect("localhost", "root", "", "to_inventory");
if(!$conn){
  die("Database connection failed" . mysqli_connect_error());
}

if(isset($_GET['id'])){
  $id = $_GET['id'];
  
  $sql = "DELETE FROM users WHERE id = ?";
  $stmt = mysqli_prepare($conn, $sql);
  mysqli_stmt_bind_param($stmt, "i", $id);
  
  if(mysqli_stmt_execute($stmt)){
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    header("Location: get-account.php?deleted=1");
    exit;
  } else {
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    header("Location: get-account.php?error=1");
    exit;
  }
}
?>
