<?php

session_start();
if(!isset($_SESSION['role']) || $_SESSION['role'] !== 'ict'){
  header("Location: ../login.php");
  exit();
}

$logged_in_ict_id =$_SESSION['user_id'];
$username =$_SESSION['username'];

$tab = isset($_GET['tab']) ? $_GET['tab'] : 'all';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$date_filter = isset($_GET['date_filter']) ? $_GET['date_filter'] : 'all';
try{
  $pdo = NEW PDO("mysql:host=localhost;dbname=to_inventory", "root", "");
  $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $sql = "SELECT name FROM users WHERE id = :user_id";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':user_id', $logged_in_ict_id, PDO::PARAM_INT);
    $stmt->execute();
    $user_data = $stmt->fetch(PDO::FETCH_ASSOC);
}catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$success_message = '';
if(isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}


$travel_orders = [];
if ($tab === 'all' || $tab === 'orders' || $tab === 'completed') {
    $sql_orders = "SELECT id, name, position, destination, departure_date, created_at, status
                   FROM travel_orders WHERE 1=1";
    
    $params = [];
    
    if ($tab === 'all' || $tab === 'orders') {
        $sql_orders .= " AND status != 'completed'";
    } elseif ($tab === 'completed') {
        $sql_orders .= " AND status = 'completed'";
    }
    
    if (!empty($search)) {
        $sql_orders .= " AND name LIKE ?";
        $params[] = '%' . $search . '%';
    }
    
    if ($date_filter !== 'all' && $tab === 'completed') {
        switch($date_filter) {
            case '7days':
                $sql_orders .= " AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                break;
            case '30days':
                $sql_orders .= " AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                break;
            case 'this_month':
                $sql_orders .= " AND MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())";
                break;
        }
    }
    
    $sql_orders .= " ORDER BY created_at DESC";
    $stmt_orders = $pdo->prepare($sql_orders);
    
    foreach ($params as $i => $param) {
        $stmt_orders->bindValue($i + 1, $param, PDO::PARAM_STR);
    }
    
    $stmt_orders->execute();
    $travel_orders = $stmt_orders->fetchAll(PDO::FETCH_ASSOC);
}

/* FETCH TRAVEL CLEARANCES */
$travel_clearances = [];
if ($tab === 'all' || $tab === 'clearances' || $tab === 'completed') {
    $sql_clearances = "SELECT id, name, location, travel_date, created_at, status
                       FROM travel_clearances WHERE 1=1";
    
    $params_tc = [];
    
    if ($tab === 'all' || $tab === 'clearances') {
        $sql_clearances .= " AND status = 'pending_planner'";
    } elseif ($tab === 'completed') {
        $sql_clearances .= " AND status = 'approved_planner'";
    }
    
    if (!empty($search)) {
        $sql_clearances .= " AND name LIKE ?";
        $params_tc[] = '%' . $search . '%';
    }
    
    if ($date_filter !== 'all' && $tab === 'completed') {
        switch($date_filter) {
            case '7days':
                $sql_clearances .= " AND created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)";
                break;
            case '30days':
                $sql_clearances .= " AND created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
                break;
            case 'this_month':
                $sql_clearances .= " AND MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW())";
                break;
        }
    }
    
    $sql_clearances .= " ORDER BY created_at DESC";
    $stmt_clearances = $pdo->prepare($sql_clearances);
    
    foreach ($params_tc as $i => $param) {
        $stmt_clearances->bindValue($i + 1, $param, PDO::PARAM_STR);
    }
    
    $stmt_clearances->execute();
    $travel_clearances = $stmt_clearances->fetchAll(PDO::FETCH_ASSOC);
}

$total_items = count($travel_orders) + count($travel_clearances);

/* COUNT PENDING APPROVALS */
$pending_orders_count = 0;
$pending_clearances_count = 0;

if (empty($search)) {
    $stmt_pending_orders = $pdo->query("SELECT COUNT(*) FROM travel_orders WHERE status != 'completed'");
    $pending_orders_count = $stmt_pending_orders->fetchColumn();
    
    $stmt_pending_clearances = $pdo->query("SELECT COUNT(*) FROM travel_clearances WHERE status = 'pending_planner'");
    $pending_clearances_count = $stmt_pending_clearances->fetchColumn();
}


/* DELETE TRAVEL CLEARANCE */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_clearance_btn'])) {
    $delete_id = (int) $_POST['delete_id'];

    $delete_sql = "DELETE FROM travel_clearances WHERE id = :id";
    $delete_stmt = $pdo->prepare($delete_sql);
    $delete_stmt->bindParam(':id', $delete_id, PDO::PARAM_INT);
    $delete_stmt->execute();

    header("Location: index.php?tab=" . urlencode($tab));
    exit();
}

/* DELETE TRAVEL ORDER */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_order_btn'])) {
    $delete_id = (int) $_POST['delete_id'];

    $get_sql = "SELECT applicant_signature FROM travel_orders WHERE id = :id";
    $get_stmt = $pdo->prepare($get_sql);
    $get_stmt->bindParam(':id', $delete_id, PDO::PARAM_INT);
    $get_stmt->execute();
    $order = $get_stmt->fetch(PDO::FETCH_ASSOC);
    
    if($order && !empty($order['applicant_signature'])){
        $file_path = '../' . $order['applicant_signature'];
        if(file_exists($file_path)){
            unlink($file_path);
        }
    }

    $delete_sql = "DELETE FROM travel_orders WHERE id = :id";
    $delete_stmt = $pdo->prepare($delete_sql);
    $delete_stmt->bindParam(':id', $delete_id, PDO::PARAM_INT);
    $delete_stmt->execute();

    header("Location: index.php?tab=" . urlencode($tab));
    exit();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>TO - ICT Account</title>
  <link rel="stylesheet" type="text/css" href="../style/email-style.css">
  <link rel="stylesheet" type="text/css" href="../style/ict-style.css">
  <link rel="stylesheet" type="text/css" href="../style/delete-btn.css">
  <link rel = "stylesheet" type ="text/css" href="../style/pop-up-message.css">
  <?php include '../includes/header.php';?>
</head>
<body>
 <div class="dashboard-container">
        <div class="tabs">
            <a href="index.php?tab=all" class="tab-link <?php echo ($tab === 'all') ? 'active' : ''; ?>">All</a>
            <a href="index.php?tab=orders" class="tab-link <?php echo ($tab === 'orders') ? 'active' : ''; ?>">
                Travel Orders
                <?php if ($pending_orders_count > 0 && empty($search)): ?>
                    <span class="notification-badge"><?php echo $pending_orders_count; ?></span>
                <?php endif; ?>
            </a>
            <a href="index.php?tab=clearances" class="tab-link <?php echo ($tab === 'clearances') ? 'active' : ''; ?>">
                Travel Clearance
                <?php if ($pending_clearances_count > 0 && empty($search)): ?>
                    <span class="notification-badge"><?php echo $pending_clearances_count; ?></span>
                <?php endif; ?>
            </a>
            <a href="index.php?tab=completed" class="tab-link <?php echo ($tab === 'completed') ? 'active' : ''; ?>">Completed</a>
            <div class="search-container">
            <form method="GET" action="" style="display: flex; gap: 10px; align-items: center;">
                <input type="hidden" name="tab" value="<?php echo htmlspecialchars($tab); ?>">
                <?php if ($tab === 'completed'): ?>
                    <select name="date_filter" class="date-filter" onchange="this.form.submit()">
                        <option value="all" <?php echo ($date_filter === 'all') ? 'selected' : ''; ?>>All Time</option>
                        <option value="7days" <?php echo ($date_filter === '7days') ? 'selected' : ''; ?>>Last 7 Days</option>
                        <option value="30days" <?php echo ($date_filter === '30days') ? 'selected' : ''; ?>>Last 30 Days</option>
                        <option value="this_month" <?php echo ($date_filter === 'this_month') ? 'selected' : ''; ?>>This Month</option>
                    </select>
                <?php endif; ?>
                <input class ="search-bar" type="text" name="search" placeholder="Search by applicant name..." value="<?php echo htmlspecialchars($search); ?>">
                <button class="submit-search" type="submit">Search</button>
                <?php if (!empty($search) || ($tab === 'completed' && $date_filter !== 'all')): ?>
                    <a class="clear-search" href="?tab=<?php echo $tab; ?>">Clear</a>
                <?php endif; ?>
            </form>
        </div>
        </div>
         <p>You have <strong><?php echo $total_items; ?></strong> items<?php echo ($tab === 'completed') ? '' : ' to review'; ?>.<?php if (!empty($search)): ?> <span style="color: #666;">(Filtered by: "<?php echo htmlspecialchars($search); ?>")</span><?php endif; ?></p>
        <?php if ($tab === 'all' || $tab === 'orders' || $tab === 'completed'): ?>
            <?php if ($tab !== 'clearances'): ?>
            <div class="section-title">Travel Orders</div>

            <?php if (count($travel_orders) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Date Submitted</th>
                            <th>Applicant Name</th>
                            <th>Position</th>
                            <th>Destination</th>
                            <th>Departure Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($travel_orders as $order): 
                            switch($order['status']){
                                case 'pending_do':
                                    $status_change = 'Waiting for Division Chief Approval';
                                    break;
                                case 'pending_rd':
                                    $status_change = 'Waiting for Regional Director Approval';
                                    break;
                                case 'approved':
                                    $status_change = 'Travel Order Approved';
                                    break;
                                case 'completed':
                                    $status_change = 'Travel Order Completed';
                                    break;
                                default:
                                    $status_change = 'Unknown Status';
                            }
                        ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                <td><?php echo htmlspecialchars($order['name']); ?></td>
                                <td><?php echo htmlspecialchars($order['position']); ?></td>
                                <td><?php echo htmlspecialchars($order['destination']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($order['departure_date'])); ?></td>
                                <td><span class="badge"><?php echo htmlspecialchars($status_change); ?></span></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="review_to.php?id=<?php echo $order['id']; ?>" class="btn-review">Review</a>
                                        <button type="button" class="btn-delete" onclick="openDeleteOrderModal(<?php echo $order['id']; ?>)">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php elseif ($tab === 'orders'): ?>
                <div class="empty-state">
                    <h3>No travel orders found.</h3>
                    <p>There are no travel orders available right now.</p>
                </div>
            <?php elseif ($tab === 'completed' && count($travel_orders) === 0): ?>
                <div class="empty-state">
                    <h3>No completed travel orders found.</h3>
                    <p>There are no completed travel orders for the selected period.</p>
                </div>
            <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($tab === 'all' || $tab === 'clearances' || $tab === 'completed'): ?>
            <?php if ($tab !== 'orders'): ?>
            <div class="section-title">Travel Clearance</div>

            <?php if (count($travel_clearances) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Date Submitted</th>
                            <th>Name of Fieldmen</th>
                            <th>Location</th>
                            <th>Travel Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($travel_clearances as $tc): 
                            switch($tc['status']){
                                case 'pending_planner':
                                    $status_change = 'Waiting for Planner Approval';
                                    break;
                                case 'approved_planner':
                                    $status_change = 'Travel Clearance Approved by Planner';
                                    break;
                                default:
                                    $status_change = 'Unknown Status';
                            }
                            
                            ?>
                            
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($tc['created_at'])); ?></td>
                                <td><?php echo htmlspecialchars($tc['name']); ?></td>
                                <td><?php echo htmlspecialchars($tc['location']); ?></td>
                                <td><?php echo htmlspecialchars($tc['travel_date']); ?></td>
                                <td><span class="badge"><?php echo htmlspecialchars($status_change); ?></span></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="review_tc.php?id=<?php echo $tc['id']; ?>" class="btn-review">Review</a>
                                        <button type="button" class="btn-delete" onclick="openDeleteClearanceModal(<?php echo $tc['id']; ?>)">Delete</button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php elseif ($tab === 'clearances'): ?>
                <div class="empty-state">
                    <h3>No travel clearances found.</h3>
                    <p>There are no travel clearances available right now.</p>
                </div>
            <?php elseif ($tab === 'completed' && count($travel_clearances) === 0): ?>
                <div class="empty-state">
                    <h3>No completed travel clearances found.</h3>
                    <p>There are no completed travel clearances for the selected period.</p>
                </div>
            <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($tab === 'all' && count($travel_orders) === 0 && count($travel_clearances) === 0): ?>
            <div class="empty-state">
                <h3>All caught up!</h3>
                <p>There are no travel orders/clearances items requiring your attention right now.</p>
            </div>
        <?php endif; ?>
        <!-- travel order modal-->
    <div id="deleteOrderModal" class="modal">
        <div class="modal-content">
            <h3>Confirm Delete</h3>
            <p>Are you sure you want to delete this travel order?</p>
            <form method="post" id="deleteOrderForm">
                <input type="hidden" name="delete_id" id="deleteOrderId">
                <button class = "confirm-btn" type="submit" name="delete_order_btn">Yes, Delete</button>
                <button class = "cancel-btn" type="button" onclick="closeDeleteOrderModal()">Cancel</button>
            </form> 
        </div>
    </div>
          <!-- travel clearance modal-->
    <div id="deleteClearanceModal" class="modal">
        <div class="modal-content">
            <h3>Confirm Delete</h3>
            <p>Are you sure you want to delete this travel clearance?</p>
            <form method="post" id="deleteClearanceForm">
                <input type="hidden" name="delete_id" id="deleteClearanceId">
                <button class="confirm-btn" type="submit" name="delete_clearance_btn">Yes, Delete</button>
                <button class ="cancel-btn" type="button" onclick="closeDeleteClearanceModal()">Cancel</button>
            </form>
        </div>
    </div>

    <div class="popup-overlay" id="popupOverlay"></div>
    <div class="popup-message" id="popupMessage">
        <p id="popupText"></p>
        <button onclick="closePopup()">OK</button>
    </div>
    <script src="ict-script.js"></script>
    <script>
        function openDeleteOrderModal(id) {
            document.getElementById('deleteOrderId').value = id;
            document.getElementById('deleteOrderModal').style.display = 'block';
        }

        function closeDeleteOrderModal() {
            document.getElementById('deleteOrderModal').style.display = 'none';
        }

        function openDeleteClearanceModal(id) {
            document.getElementById('deleteClearanceId').value = id;
            document.getElementById('deleteClearanceModal').style.display = 'block';
        }

        function closeDeleteClearanceModal() {
            document.getElementById('deleteClearanceModal').style.display = 'none';
        }

        function closePopup() {
            document.getElementById('popupMessage').style.display = 'none';
            document.getElementById('popupOverlay').style.display = 'none';
        }

        <?php if(!empty($success_message)): ?>
        window.onload = function() {
            document.getElementById('popupText').textContent = '<?php echo $success_message; ?>';
            document.getElementById('popupOverlay').style.display = 'block';
            document.getElementById('popupMessage').style.display = 'block';
        };
        <?php endif; ?>
    </script>
</body>
</html>