<?php 

session_start();
if($_SESSION['role']!=='ict'){
  die('access denied!');
}

$conn = mysqli_connect("localhost", "root", "", "to_inventory");
if(!$conn){
  die("Database connection failed" . mysqli_connect_error());
}

if($_SERVER['REQUEST_METHOD']==='POST'){
  $role = $_POST['role'];
  $fullname =$_POST['name'];
  $username = $_POST['username'];
  $password = password_hash($_POST['password'], PASSWORD_DEFAULT);

  $check_sql = "SELECT id FROM users WHERE username = ?";
  $check_stmt = mysqli_prepare($conn, $check_sql);
  mysqli_stmt_bind_param($check_stmt, "s", $username);
  mysqli_stmt_execute($check_stmt);
  mysqli_stmt_store_result($check_stmt);

  if(mysqli_stmt_num_rows($check_stmt) > 0){
    echo json_encode(['success' => false, 'message' => 'Username already taken']);
    mysqli_stmt_close($check_stmt);
    mysqli_close($conn);
    exit;
  }
  mysqli_stmt_close($check_stmt);

  $sql = "INSERT INTO users(role,name,username,password)
          VALUES (?,?,?,?)";

  $stmt = mysqli_prepare($conn, $sql);
  if($stmt){
    mysqli_stmt_bind_param(
      $stmt,
      "ssss",
      $role,
      $fullname,
      $username,
      $password
    );

    if(mysqli_stmt_execute($stmt)){
      echo json_encode(['success' => true, 'message' => 'Account created successfully']);
    }else {
      echo json_encode(['success' => false, 'message' => 'Error saving to database']);
    }

    mysqli_stmt_close($stmt);
  } else {
    echo json_encode(['success' => false, 'message' => 'Error preparing statement']);
  }

  mysqli_close($conn);
}
?>