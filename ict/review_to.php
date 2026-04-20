<?php 

session_start();

try {
    $conn = new PDO("mysql:host=localhost;dbname=to_inventory", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch(PDOException $e) {
    die("Connection failed: " . $e->getMessage());
}

if(!isset($_SESSION['user_id'])|| !isset($_SESSION['username'])|| !isset($_SESSION['role'])){
  header("location: ../index.php");
  exit();
}
if($_SESSION['role'] !== 'ict'){
  die("Access Denied");
}

$logged_in_ict_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

if(!isset($_GET['id'])|| empty($_GET['id'])){
  die("Error: No Travel ID provided"); 
}

$order_id = (int)$_GET['id'];

$sql = "SELECT * FROM travel_orders WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$order){
  die("Travel Order not found.");
}

$current_status = $order['status'];
$officer_id = $order['officer_id'];

$do_signature = '';
if(!empty($officer_id)){
  $do_sql = "SELECT user_signature FROM users WHERE id = ?";
  $do_stmt = $conn->prepare($do_sql);
  $do_stmt->execute([$officer_id]);
  $do_data = $do_stmt->fetch(PDO::FETCH_ASSOC);
  if($do_data){
    $do_signature = $do_data['user_signature'];
  }
}
$rd_signature = '';
$rd_sql = "SELECT user_signature FROM users WHERE role = 'rd' LIMIT 1";
$rd_stmt = $conn->prepare($rd_sql);
$rd_stmt->execute();
$rd_data = $rd_stmt->fetch(PDO::FETCH_ASSOC);
if($rd_data){
  $rd_signature = $rd_data['user_signature'];
}

if($_SERVER["REQUEST_METHOD"] == "POST"){
  
  if($current_status == 'approved' && isset($_POST['complete_btn'])){
    $update_sql = "UPDATE travel_orders SET status = 'completed' WHERE id = ? AND status = 'approved'";
    $update_stmt = $conn->prepare($update_sql);
    
    if($update_stmt->execute([$order_id])){
      $_SESSION['success_message'] = 'Travel Order completed successfully!';
      header("location: index.php?tab=orders");
      exit();
    }
    
  } elseif($current_status == 'pending_rd' && isset($_POST['approve_btn'])){
    $update_sql = "UPDATE travel_orders SET status = 'approved', rd_signature = ? WHERE id = ? AND status = 'pending_rd'";
    $update_stmt = $conn->prepare($update_sql);
    
    if($update_stmt->execute([$rd_signature, $order_id])){
      $_SESSION['success_message'] = 'Travel Order approved successfully!';
      header("location: index.php?tab=orders");
      exit();
    }
    
  } elseif($current_status == 'pending_do' && isset($_POST['approve_btn'])){
    $update_sql = "UPDATE travel_orders SET status = 'pending_rd', do_signature = ? WHERE id = ? AND status = 'pending_do'";
    $update_stmt = $conn->prepare($update_sql);
    
    if($update_stmt->execute([$do_signature, $order_id])){
      $_SESSION['success_message'] = 'Travel Order forwarded to RD successfully!';
      header("location: index.php?tab=orders");
      exit();
    }
  } elseif(isset($_POST['attach_btn'])){
      if($current_status == 'pending_do'){
        $update_sql = "UPDATE travel_orders SET status = 'approved', rd_signature = ?, do_signature = ? WHERE id = ? AND status = 'pending_do'";
        $update_stmt = $conn->prepare($update_sql);

        if($update_stmt->execute([$rd_signature, $do_signature, $order_id])){
          $_SESSION['success_message'] = 'Travel Order approved and forwarded to Planner successfully';
          header("location: index.php?tab=orders");
          exit();
        }
      }elseif($current_status == 'pending_rd'){
        $update_sql = "UPDATE travel_orders SET status = 'approved', rd_signature = ? WHERE id = ? AND status = 'pending_rd'";
        $update_stmt = $conn->prepare($update_sql);

        if($update_stmt->execute([$rd_signature, $order_id])){
          $_SESSION['success_message'] = 'Travel Order approved and forwarded to Planner successfully';
          header("location: index.php?tab=orders");
          exit();
        }
      }
    }
}

$purposes = json_decode($order['purpose'], true);
$assistants = json_decode($order['assistants'], true);

if(!is_array($purposes)){
  $purposes = [];
}

if(!is_array($assistants)){
  $assistants = [];
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Review Travel Order</title>
  <link rel="stylesheet" type="text/css" href="../style/review_to.css">
</head>
<body>
    <div class="box">
      <button type="button" class="btn btn-back" onclick="window.location.href='index.php'">Back to Dashboard</button>
        <h1>Review Travel Order</h1>


        <div class="detail-grid">
            <div class="detail-item"><strong>Name</strong> <?php echo htmlspecialchars($order['name']); ?></div>
            <div class="detail-item"><strong>Position</strong> <?php echo htmlspecialchars($order['position']); ?></div>
            <div class="detail-item"><strong>Division/Unit</strong> <?php echo htmlspecialchars($order['division_unit']); ?></div>
            <div class="detail-item"><strong>Salary</strong> <?php echo htmlspecialchars($order['salary']); ?></div>
            <div class="detail-item"><strong>Official Station</strong> <?php echo htmlspecialchars($order['official_station']); ?></div>
            <div class="detail-item"><strong>Destination</strong> <?php echo htmlspecialchars($order['destination']); ?></div>
            <div class="detail-item"><strong>Departure Date</strong> <?php echo htmlspecialchars($order['departure_date']); ?></div>
            <div class="detail-item"><strong>Arrival Date</strong> <?php echo htmlspecialchars($order['arrival_date']); ?></div>

            <div class="detail-item full-width">
                <strong>Purpose of Travel</strong>
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

            <div class="detail-item full-width">
                <strong>Assistants Allowed</strong>
                <ul>
                    <?php
                    if (!empty($assistants)) {
                        foreach ($assistants as $a) {
                            echo "<li>" . htmlspecialchars($a) . "</li>";
                        }
                    } else {
                        echo "<li>None specified</li>";
                    }
                    ?>
                </ul>
            </div>

            <div class="detail-item"><strong>Per Diems/Expenses</strong> <?php echo htmlspecialchars($order['per_diems']); ?></div>
            <div class="detail-item"><strong>Appropriation</strong> <?php echo htmlspecialchars($order['appropriation']); ?></div>
            <div class="detail-item full-width"><strong>Remarks</strong> <?php echo htmlspecialchars($order['remarks']); ?></div>
        </div>

        <div class="signature-box">
            <strong>Applicant E-Signature</strong><br>
            <?php if (!empty($order['applicant_signature'])): ?>
                <img src="../<?php echo htmlspecialchars($order['applicant_signature']); ?>" alt="Applicant Signature">
            <?php else: ?>
                <p>No applicant signature uploaded.</p>
            <?php endif; ?>
        </div>

        <div class="signature-box">
            <strong>Chief Signature</strong><br>
            <?php if (!empty($order['do_signature'])): ?>
                <img src="<?php echo htmlspecialchars($order['do_signature']); ?>" alt="Chief Signature">
            <?php else: ?>
                <p>No chief signature attached.</p>
            <?php endif; ?>
        </div>

        <div class="signature-box">
            <strong>Regional Director Signature</strong><br>
            <?php if (!empty($order['rd_signature'])): ?>
                <img src="<?php echo htmlspecialchars($order['rd_signature']); ?>" alt="Regional Director Signature">
            <?php else: ?>
                <p>No Regional Director signature attached.</p>
            <?php endif; ?>
        </div>

    <form method="POST" style="margin-top: 30px;">
      <?php if($current_status == 'pending_do'): ?>
        <button type="submit" name="approve_btn" class="btn btn-approve">Forward to RD</button>
        <button type="button" class="btn attach-btn" onclick="checkSignatures('pending_do')">Attach Signatures and send to Planner</button>
      <?php elseif($current_status == 'pending_rd'): ?>
        <button type="submit" name="approve_btn" class="btn btn-approve">Approve Order</button>
        <button type="button" class="btn attach-btn" onclick="checkSignatures('pending_rd')">Attach Signatures and send to Planner</button>
      <?php elseif($current_status == 'approved'): ?>
        <button type="submit" name="complete_btn" class="btn btn-approve">Mark as Completed</button>
        <a class="btn btn-download-pdf" href="adjust_signatures_to.php?id=<?php echo $order['id']; ?>">Adjust Signatures & Download PDF</a>
      <?php elseif($current_status == 'completed'): ?>
        <div class="modal-overlay" id="completedModal">
          <div class="modal-content">
            <p><strong>This travel order has been completed.</strong></p>
            <button class="closeBtn" type="button" id="closeBtn">Ok</button>
          </div>
        </div>
        <a class="btn btn-download-pdf" href="adjust_signatures_to.php?id=<?php echo $order['id']; ?>">Adjust Signatures & Download PDF</a>
      <?php endif; ?>
    </form>
  </div>
<div id="signatureModal" style="display:none; position:fixed; z-index:1000; left:0; top:0; width:100%; height:100%; background:rgba(0,0,0,0.5);">
  <div style="background:#fff; margin:10% auto; padding:30px; width:450px; border-radius:8px; box-shadow:0 4px 8px rgba(0,0,0,0.2);">
    <h3 style="margin-top:0; color:#333;">Attach Signatures Confirmation</h3>
    <div id="modalMessage" style="color:#666; margin:20px 0; line-height:1.6;"></div>
    <div style="margin-top:25px;">
      <button onclick="confirmAttach()" style="background:#4CAF50; color:white; border:none; padding:10px 20px; border-radius:4px; cursor:pointer; margin-right:10px;">Yes, Proceed</button>
      <button onclick="closeSignatureModal()" style="background:#999; color:white; border:none; padding:10px 20px; border-radius:4px; cursor:pointer;">Cancel</button>
    </div>
  </div>
</div>

<script>
let currentStatus = null;

function checkSignatures(status) {
  currentStatus = status;
  let missing = [];
  let message = '';
  
  if(status === 'pending_do') {
    if(!'<?php echo !empty($do_signature); ?>') missing.push('Chief Signature');
    if(!'<?php echo !empty($rd_signature); ?>') missing.push('Regional Director Signature');
    
    if(missing.length > 0) {
      message = '<strong>Missing signatures:</strong><br>' + missing.join('<br>') + '<br><br>Do you want to attach available signatures and approve this travel order?';
    } else {
      message = 'This will attach both Chief and Regional Director signatures and approve the travel order.<br><br>Do you want to proceed?';
    }
  } else if(status === 'pending_rd') {
    if(!'<?php echo !empty($rd_signature); ?>') missing.push('Regional Director Signature');
    
    if(missing.length > 0) {
      message = '<strong>Missing signature:</strong><br>' + missing.join('<br>') + '<br><br>Do you want to proceed anyway and approve this travel order?';
    } else {
      message = 'This will attach the Regional Director signature and approve the travel order.<br><br>Do you want to proceed?';
    }
  }
  
  document.getElementById('modalMessage').innerHTML = message;
  document.getElementById('signatureModal').style.display = 'block';
}

function confirmAttach() {
  let form = document.createElement('form');
  form.method = 'POST';
  form.innerHTML = '<input type="hidden" name="attach_btn" value="1">';
  document.body.appendChild(form);
  form.submit();
}

function closeSignatureModal() {
  document.getElementById('signatureModal').style.display = 'none';
  currentStatus = null;
}
 <?php if($order['status'] == 'completed'): ?>
    window.onload = function(){
      document.getElementById('completedModal').classList.add('show');
    };

    document.getElementById('closeBtn').onclick = function(){
      document.getElementById('completedModal').classList.remove('show');
    };
    <?php endif; ?>
</script>
</body>
</html>