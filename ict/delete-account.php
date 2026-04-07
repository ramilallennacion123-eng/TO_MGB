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

if(isset($_GET['id'])){
  $id = $_GET['id'];
  
  $sql = "DELETE FROM users WHERE id = ?";
  $stmt = $conn->prepare($sql);
  
  if($stmt->execute([$id])){
    header("Location: get-account.php?deleted=1");
    exit;
  } else {
    header("Location: get-account.php?error=1");
    exit;
  }
}
?>
