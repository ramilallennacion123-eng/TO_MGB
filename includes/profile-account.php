<?php 

session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit();
}

$conn = mysqli_connect("localhost", "root", "", "to_inventory");
if(!$conn){
  die("Connection failed: " . mysqli_connect_error());
}

$sql = "SELECT id, name, username, position, role  FROM users WHERE id = ?";
$check_stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($check_stmt, "i", $_SESSION['user_id']);
mysqli_stmt_execute($check_stmt);
$result = mysqli_stmt_get_result($check_stmt);
$user_data = mysqli_fetch_assoc($result);
$test_position = $user_data['position'] ?? '';
$user_id['id'] = $user_data['id'];
$user_name['username'] = $user_data['username'];

mysqli_stmt_close($check_stmt);

$success_msg = '';
$error_msg = '';

if($_SERVER['REQUEST_METHOD'] === 'POST'){
  $name = $_POST['name'];
  $username = $_POST['username'];
  $position = $_POST['position'];
  $password = $_POST['password'];
  $signature_path = '';

  if (isset($_FILES['e_signature']) && $_FILES['e_signature']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = '../uploads/signatures/';

    if (!is_dir($upload_dir)) {
      mkdir($upload_dir, 0777, true);
    }

    $file_tmp_path = $_FILES['e_signature']['tmp_name'];
    $file_name = $_FILES['e_signature']['name'];
    $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
    $allowed_exts = array('jpg', 'jpeg', 'png');

    if (in_array($file_extension, $allowed_exts)) {
      $new_file_name = $user_data['role'] . '_' . $user_data['id'] . '.' . $file_extension;
      $destination_path = $upload_dir . $new_file_name;

      if (move_uploaded_file($file_tmp_path, $destination_path)) {
        $signature_path = $destination_path;
      } else {
        $error_msg = "Error uploading signature file.";
      }
    } else {
      $error_msg = "Invalid file type. Only JPG, JPEG, and PNG are allowed.";
    }
  }

  if(empty($error_msg)){
    if (!empty($signature_path) && !empty($password)) {
      $hashed_password = password_hash($password, PASSWORD_DEFAULT);
      $update_sql = "UPDATE users SET name = ?, position = ?, username = ?, password = ?, user_signature = ? WHERE id = ?";
      $update_stmt = mysqli_prepare($conn, $update_sql);
      mysqli_stmt_bind_param($update_stmt, "sssssi", $name, $position, $username, $hashed_password, $signature_path, $_SESSION['user_id']);
    } elseif (!empty($signature_path)) {
      $update_sql = "UPDATE users SET name = ?, position = ?, username = ?, user_signature = ? WHERE id = ?";
      $update_stmt = mysqli_prepare($conn, $update_sql);
      mysqli_stmt_bind_param($update_stmt, "ssssi", $name,$position, $username, $signature_path, $_SESSION['user_id']);
    } elseif (!empty($password)) {
      $hashed_password = password_hash($password, PASSWORD_DEFAULT);
      $update_sql = "UPDATE users SET name = ?, position = ?, username = ?, password = ? WHERE id = ?";
      $update_stmt = mysqli_prepare($conn, $update_sql);
      mysqli_stmt_bind_param($update_stmt, "ssssi", $name, $position, $username, $hashed_password, $_SESSION['user_id']);
    } else {
      $update_sql = "UPDATE users SET name = ?, position = ?, username = ? WHERE id = ?";
      $update_stmt = mysqli_prepare($conn, $update_sql);
      mysqli_stmt_bind_param($update_stmt, "sssi", $name, $position, $username, $_SESSION['user_id']);
    }

    if(mysqli_stmt_execute($update_stmt)){
      $_SESSION['username'] = $username;
      $success_msg = "Profile updated successfully!";
      $user_data['name'] = $name;
      $user_data['username'] = $username;
      $user_data['position'] = $position;
      $test_position = $position;
      $user_name['username'] = $username;
    } else {
      $error_msg = "Error updating profile. Please try again.";
    }
    mysqli_stmt_close($update_stmt);
  }
}

mysqli_close($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profile Account</title>
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f4f4f4;
      padding: 20px;
    }
    .container {
      max-width: 600px;
      margin: 0 auto;
      background: #fff;
      padding: 40px;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    h2 {
      color: #333;
      text-align: center;
      margin-bottom: 30px;
    }
    .form-group {
      margin-bottom: 20px;
    }
    label {
      display: block;
      margin-bottom: 8px;
      color: #333;
      font-weight: bold;
    }
    input[type="text"],
    input[type="password"],
    input[type="email"],
    input[type="file"],
    select {
      width: 100%;
      padding: 12px;
      border: 1px solid #ddd;
      border-radius: 4px;
      box-sizing: border-box;
      font-size: 14px;
    }
    input[type="file"] {
      padding: 8px;
      cursor: pointer;
      background: #f9f9f9;
    }
    input[type="file"]:hover {
      background: #f0f0f0;
    }
    input[type="text"]:focus,
    input[type="password"]:focus,
    input[type="file"]:focus,
    select:focus {
      outline: none;
      border-color: #4CAF50;
      box-shadow: 0 0 5px rgba(76, 175, 80, 0.3);
    }
    .form-row {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }
    .button-group {
      display: flex;
      gap: 10px;
      margin-top: 30px;
    }
    button {
      flex: 1;
      padding: 12px;
      border: none;
      border-radius: 4px;
      font-size: 16px;
      cursor: pointer;
      font-weight: bold;
    }
    .btn-update {
      background: #4CAF50;
      color: white;
    }
    .btn-update:hover {
      background: #45a049;
    }
    .btn-back {
      background: #999;
      color: white;
    }
    .btn-back:hover {
      background: #888;
    }
    .success-msg {
      background: #d4edda;
      color: #155724;
      padding: 12px;
      border-radius: 4px;
      margin-bottom: 20px;
      border: 1px solid #c3e6cb;
    }
    .error-msg {
      background: #f8d7da;
      color: #721c24;
      padding: 12px;
      border-radius: 4px;
      margin-bottom: 20px;
      border: 1px solid #f5c6cb;
    }
    .info-box {
      background: #e7f3ff;
      padding: 12px;
      border-radius: 4px;
      margin-bottom: 20px;
      border-left: 4px solid #2196F3;
    }
    .info-box p {
      margin: 5px 0;
      color: #333;
    }
  </style>
  <?php include '../includes/header.php';?>
</head>
<body>
  <div class="container">
    <h2>My Profile</h2>

    <?php if($success_msg): ?>
      <div class="success-msg"><?php echo $success_msg; ?></div>
    <?php endif; ?>

    <?php if($error_msg): ?>
      <div class="error-msg"><?php echo $error_msg; ?></div>
    <?php endif; ?>

    <div class="info-box">
      <p><strong>Role:</strong> <?php echo htmlspecialchars(strtoupper($user_data['role'])); ?></p>
      <p><strong>User ID:</strong> <?php echo htmlspecialchars($user_id['id']); ?></p>
    </div>

    <form method="POST" enctype="multipart/form-data">
      <div class="form-group">
        <label for="name">Full Name</label>
        <input type="text" id="name" name="name" value="<?php echo htmlspecialchars($user_data['name']); ?>" required>
      </div>

      <div class="form-group">
        <label for="position">Position</label>
        <input type="text" id="position" name="position" value="<?php echo htmlspecialchars($test_position); ?>" required>
      </div>

      <div class="form-group">
        <label for="username">Username</label>
        <input type="text" id="username" name="username" value="<?php echo htmlspecialchars($user_name['username']); ?>">
      </div>

      <div class="form-group">
        <label for="password">New Password</label>
        <input type="password" id="password" name="password" placeholder="Enter new password">
        <small style="color: #666; font-size: 12px; display: block; margin-top: 5px;">**Leave blank to keep current password</small>
      </div>
      <div class="form-group">
        <label for="e_signature">Attach Signature Here (Optional - JPG, JPEG, PNG only)</label>
        <input type="file" name="e_signature" id="e_signature" accept="image/jpeg,image/jpg,image/png">
        <small style="color: #666; font-size: 12px; display: block; margin-top: 5px;">Leave blank to keep current signature</small>
      </div> 

      <div class="button-group">
        <button type="submit" class="btn-update">Update Profile</button>
        <button type="button" class="btn-back" onclick="redirectToUserIndex()">Back</button>
      </div> 


    </form>
  </div>
  
  <script>
  function redirectToUserIndex() {
    const role = '<?php echo $_SESSION['role']; ?>';
    switch(role) {
      case 'admin':
        window.location.href = '../admin/index.php';
        break;
      case 'chief':
        window.location.href = '../chief/division_officer.php';
        break;
      case 'ict':
        window.location.href = '../ict/index.php';
        break;
      case 'planner':
        window.location.href = '../planner/index.php';
        break;
      default:
        window.location.href = '../';
    }
  }
  </script>
</body>
</html>
