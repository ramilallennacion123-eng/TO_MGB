<?php 
session_start();
if($_SESSION['role'] !== 'ict'){
  die('Access Denied!');
}

try {
    $conn = new PDO("mysql:host=localhost;dbname=to_inventory", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
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
    $stmt = $conn->prepare($sql);
    $result = $stmt->execute([$name, $role, $position, $username, $hashed_password, $id]);
  } else {
    $sql = "UPDATE users SET name = ?, role = ?, position = ?, username = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $result = $stmt->execute([$name, $role, $position, $username, $id]);
  }

  if($result){
    header("Location: get-account.php?success=1");
    exit;
  } else {
    header("Location: get-account.php?error=1");
    exit;
  }
}
?>
