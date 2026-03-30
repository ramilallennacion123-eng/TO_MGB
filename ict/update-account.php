<?php 
session_start();
if($_SESSION['role'] !== 'ict'){
  die('Access Denied!');
}

$conn = mysqli_connect("localhost", "root", "", "to_inventory");
if(!$conn){
  die("Database connection failed" . mysqli_connect_error());
}

if($_SERVER['REQUEST_METHOD'] === 'POST'){
  $id = $_POST['id'];
  $name = $_POST['name'];
  $role = $_POST['role'];
  $position = $_POST['position'];
  $username = $_POST['username'];
  $password = $_POST['password'];

  if(!empty($password)){
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    $sql = "UPDATE users SET name = ?, role = ?, position = ?, username = ?, password = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "sssssi", $name, $role,$position, $username, $hashed_password, $id);
  } else {
    $sql = "UPDATE users SET name = ?, role = ?, position = ?, username = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "ssssi", $name, $role, $position, $username, $id);
  }

  if(mysqli_stmt_execute($stmt)){
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    header("Location: get-account.php?success=1");
    exit;
  } else {
    mysqli_stmt_close($stmt);
    mysqli_close($conn);
    header("Location: get-account.php?error=1");
    exit;
  }
}
?>
