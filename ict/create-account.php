<?php 

session_start();
if($_SESSION['role']!=='ict'){
  die('access denied!');
}

try {
    $conn = new PDO("mysql:host=localhost;dbname=to_inventory", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

if($_SERVER['REQUEST_METHOD']==='POST'){
  $role = $_POST['role'];
  $fullname =$_POST['name'];
  $username = $_POST['username'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

  $check_sql = "SELECT id FROM users WHERE username = ?";
  $check_stmt = $conn->prepare($check_sql);
  $check_stmt->execute([$username]);

  if($check_stmt->rowCount() > 0){
    echo json_encode(['success' => false, 'message' => 'Username already taken']);
    exit;
  }

  $sql = "INSERT INTO users(role,name,username,password)
          VALUES (?,?,?,?)";

  $stmt = $conn->prepare($sql);
  if($stmt){
    if($stmt->execute([$role, $fullname, $username, $password])){
      echo json_encode(['success' => true, 'message' => 'Account created successfully']);
    }else {
      echo json_encode(['success' => false, 'message' => 'Error saving to database']);
    }
  } else {
    echo json_encode(['success' => false, 'message' => 'Error preparing statement']);
  }
}
?>