<?php 
session_start();
if($_SESSION['role'] !=='ict'){
  die("Access Denied!");
}

try {
    $conn = new PDO("mysql:host=localhost;dbname=to_inventory", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$sql = "SELECT * FROM users";
$query = $conn->query($sql);
while ($row = $query->fetch(PDO::FETCH_ASSOC)){
  $id = $row['id'];
  echo '
    <tr>
      <td>'.$row["id"].'</td>
      <td>'.$row["name"].'</td>
      <td>'.strtoupper($row["role"]).'</td>
       <td>'.$row["position"].'</td>
      <td>'.$row["username"].'</td>
      <td>
        <button class="btn-edit" onclick="openEditModal('.$id.', \''.htmlspecialchars($row["name"], ENT_QUOTES).'\', \''.htmlspecialchars($row["role"], ENT_QUOTES).'\', \''.htmlspecialchars($row["username"], ENT_QUOTES).'\', \''.htmlspecialchars($row["position"], ENT_QUOTES).'\')" style="background:#2196F3; color:white; border:none; padding:6px 12px; border-radius:4px; cursor:pointer; margin-right:5px;">Edit</button>
        <button class="btn" onclick="deleteUser('.$id.')">Delete</button>
      </td>
    </tr>
  ';
}
?>

<div id="editModal" class="modal" style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.5);">
  <div class="modal-content" style="background:#fff; margin:5% auto; padding:30px; width:400px; border-radius:8px; box-shadow:0 4px 8px rgba(0,0,0,0.2);">
    <h3 style="margin-top:0; color:#333;">Update User Account</h3>
    <form method="POST" action="update-account.php" id="editForm">
      <input type="hidden" name="id" id="edit_id">
      <input type="text" name="name" id="edit_name" placeholder="Full Name" required style="width:100%; padding:10px; margin:10px 0; border:1px solid #ddd; border-radius:4px; box-sizing:border-box;">
      <select name="role" id="edit_role" required style="width:100%; padding:10px; margin:10px 0; border:1px solid #ddd; border-radius:4px; box-sizing:border-box;">
        <option value="">Select Role</option>
        <option value="ict">ICT</option>
        <option value="planner">Planner</option>
        <option value="rd">RD</option>
        <option value="chief">Chief</option>
      </select>
      <input type="text" name="position" id="edit_position" placeholder="Position" style="width:100%; padding:10px; margin:10px 0; border:1px solid #ddd; border-radius:4px; box-sizing:border-box;">
      <input type="text" name="username" id="edit_username" placeholder="Username" required style="width:100%; padding:10px; margin:10px 0; border:1px solid #ddd; border-radius:4px; box-sizing:border-box;">
      <input type="password" name="password" placeholder="New Password (leave blank to keep current)" style="width:100%; padding:10px; margin:10px 0; border:1px solid #ddd; border-radius:4px; box-sizing:border-box;">
      <div style="margin-top:20px;">
        <button type="submit" style="background:#4CAF50; color:white; border:none; padding:10px 20px; border-radius:4px; cursor:pointer; margin-right:10px;">Update</button>
        <button type="button" onclick="closeEditModal()" style="background:#999; color:white; border:none; padding:10px 20px; border-radius:4px; cursor:pointer;">Cancel</button>
      </div>
    </form>
  </div>
</div>

<div id="deleteModal" class="modal" style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.5);">
  <div class="modal-content" style="background:#fff; margin:15% auto; padding:30px; width:350px; border-radius:8px; box-shadow:0 4px 8px rgba(0,0,0,0.2); text-align:center;">
    <h3 style="margin-top:0; color:#333;">Confirm Delete</h3>
    <p style="color:#666; margin:20px 0;">Are you sure you want to delete this user?</p>
    <div style="margin-top:20px;">
      <button onclick="confirmDelete()" style="background:#f44336; color:white; border:none; padding:10px 20px; border-radius:4px; cursor:pointer; margin-right:10px;">Yes, Delete</button>
      <button onclick="closeDeleteModal()" style="background:#999; color:white; border:none; padding:10px 20px; border-radius:4px; cursor:pointer;">Cancel</button>
    </div>
  </div>
</div>

<script>
let deleteUserId = null;

function openEditModal(id, name, role, username, position) {
  document.getElementById('edit_id').value = id;
  document.getElementById('edit_name').value = name;
  document.getElementById('edit_role').value = role;
  document.getElementById('edit_username').value = username;
  document.getElementById('edit_position').value = position;
  document.getElementById('editModal').style.display = 'block';
}

function closeEditModal() {
  document.getElementById('editModal').style.display = 'none';
}

function deleteUser(id) {
  deleteUserId = id;
  document.getElementById('deleteModal').style.display = 'block';
}

function confirmDelete() {
  if(deleteUserId) {
    window.location.href = 'delete-account.php?id=' + deleteUserId;
  }
}

function closeDeleteModal() {
  document.getElementById('deleteModal').style.display = 'none';
  deleteUserId = null;
}
</script>
