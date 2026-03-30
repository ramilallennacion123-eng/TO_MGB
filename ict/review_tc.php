<?php 

session_start();

$conn = mysqli_connect("localhost", "root", "", "to_inventory");
if(!$conn){
  die("Database connection failed: ".mysqli_connect_error());
}

if(!isset($_SESSION['user_id'])|| !isset($_SESSION['username']) || !isset($_SESSION['role'])){
  header("location: ../login.php");
  exit();
}

if($_SESSION['role'] !== 'ict' ){
  die("Access Dinied");
}

$logged_in_ict_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

if(!isset($_GET['id'])|| empty($_GET['id'])){
  die("Error: No Travel Clearance ID provided.");
}

$clearance_id = (int) $_GET['id'];

if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['approve_btn'])){
  $update_sql = "UPDATE travel_clearances SET status = 'approved_planner' 
                 WHERE id = ? AND status = 'pending_planner'";
  
  $update_stmt = mysqli_prepare($conn , $update_sql);

  if($update_stmt){
    mysqli_stmt_bind_param($update_stmt, "i", $clearance_id);
    mysqli_stmt_execute($update_stmt);

    if(mysqli_stmt_affected_rows($update_stmt) > 0){
      mysqli_stmt_close($update_stmt);
      $_SESSION['success_message'] = 'Travel Clearance approved successfully';
      header("location: index.php");
      exit();
    }else{
      mysqli_stmt_close($update_stmt);
      die("1. Unable to approve this travel clearance. It may not belong to your account or it may already be processed.");
    }
  }else{
    die("Error updating record: " .mysqli_error($conn));
  }
}

$sql = "SELECT *
        FROM travel_clearances
        WHERE id = ?";

$stmt = mysqli_prepare($conn, $sql);

if($stmt){
  mysqli_stmt_bind_param($stmt, "i", $clearance_id);
  mysqli_stmt_execute($stmt);
  $result = mysqli_stmt_get_result($stmt);
  $clearance = mysqli_fetch_assoc($result);
  mysqli_stmt_close($stmt);
}else {
  die ("Error fetching data: ". mysqli_error($conn));
}

if(!$clearance){
  die("2. Travel Clearance not found, does not belong to your account, or it has already been processed");
}

$purposes = json_decode($clearance['purpose'], true);
if(!is_array($purposes)){
  $purposes = [];
}

mysqli_close($conn);

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Review Travel Clearance</title>
  <link rel="stylesheet"type="text/css"  href="../style/review_tc.css">
</head>
<body>
  <div class="box">
    <h1> REVIEW TRAVEL CLEARANCE</h1>
    <div class="detail-grid">
      <div class="detail-item"><strong>Name</strong> <?php echo htmlspecialchars($clearance['name']); ?></div>
      <div class="detail-item"><strong>PAP-Code</strong><?php echo htmlspecialchars($clearance['pap_code']) ?></div>
      <div class="detail-item full-width"><strong>Location</strong><?php echo htmlspecialchars($clearance['location']) ?></div>
      <div class="detail-item full-width"><strong>Travel Date</strong><?php echo htmlspecialchars($clearance['travel_date']) ?></div>
      <div class="detail-item full-width"> <strong>Purpose of Travel</strong>
        <ul>
          <?php
            if (!empty($purposes)) {
                foreach ($purposes as $p) {
                    echo "<li>" . htmlspecialchars($p) . "</li>";
                }
            } else {
                echo "<li>None specified</li>";
            }
          ?>
        </ul>
      </div>
    </div>
    

    <form method="POST" class="action-area">
       <div class="button-group">
      <?php if($clearance['status'] == 'pending_planner' ): ?>
      <button type="submit" name="approve_btn" class="btn btn-approve">Approve Travel Clearance</button>
      <?php elseif($clearance['status'] == 'approved_planner'): ?>
      <div class="modal-overlay" id="approvedModal">
        <div class="modal-content">
          <p><strong>This travel clearance has been approved.</strong></p>
          <button class = "closeBtn" type="button" id="closeBtn">Ok</button>
        </div>
      </div>
      <?php endif; ?>
     
        <a class="btn pdf-btn" href="TC_pdf.php?id=<?php echo $clearance['id']; ?>" class="btn">Download PDF</a>
        <a href="index.php" class="btn btn-cancel">Back</a>
      </div>
     
    </form>
  </div>
 

    <div class="popup-overlay" id="popupOverlay"></div>
    <div class="popup-message" id="popupMessage">
        <p id="popupText"></p>
        <button onclick="closePopup()">OK</button>
    </div>

    <script>
    function closePopup() {
        document.getElementById('popupMessage').style.display = 'none';
        document.getElementById('popupOverlay').style.display = 'none';
        window.location.href = 'index.php';
    }

    <?php if($clearance['status'] == 'approved_planner'): ?>
    window.onload = function(){
      document.getElementById('approvedModal').classList.add('show');
    };

    document.getElementById('closeBtn').onclick = function(){
      document.getElementById('approvedModal').classList.remove('show');
    };
    <?php endif; ?>
    </script>

</body>
</html>