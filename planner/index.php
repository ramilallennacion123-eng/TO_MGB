<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SESSION['role'] !== 'planner') {
    die("Access denied.");
}

$logged_in_planner_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

$success_message = '';
if(isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

$host = 'localhost';
$dbname = 'to_inventory';
$db_user = 'root';
$db_pass = '';

$tab = isset($_GET['tab']) ? $_GET['tab'] : 'all';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

/* DELETE TRAVEL CLEARANCE */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_clearance_btn'])) {
    $delete_id = (int) $_POST['delete_id'];

    $delete_sql = "DELETE FROM travel_clearances
                   WHERE id = :id AND planner_id = :planner_id";

    $delete_stmt = $pdo->prepare($delete_sql);
    $delete_stmt->bindParam(':id', $delete_id, PDO::PARAM_INT);
    $delete_stmt->bindParam(':planner_id', $logged_in_planner_id, PDO::PARAM_INT);
    $delete_stmt->execute();

    header("Location: index.php?tab=" . urlencode($tab));
    exit();
}

/* DELETE TRAVEL ORDER */
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['delete_order_btn'])) {
    $delete_id = (int) $_POST['delete_id'];

    $delete_sql = "DELETE FROM travel_orders
                   WHERE id = :id AND status = 'approved'";

    $delete_stmt = $pdo->prepare($delete_sql);
    $delete_stmt->bindParam(':id', $delete_id, PDO::PARAM_INT);
    $delete_stmt->execute();

    header("Location: index.php?tab=" . urlencode($tab));
    exit();
}

/* FETCH TRAVEL ORDERS */
$travel_orders = [];
if ($tab === 'all' || $tab === 'orders') {
    $sql_orders = "SELECT id, name, position, destination, departure_date, created_at, status
                   FROM travel_orders
                   WHERE status = 'approved'
                   ORDER BY created_at DESC";

    $stmt_orders = $pdo->prepare($sql_orders);
    $stmt_orders->execute();
    $travel_orders = $stmt_orders->fetchAll(PDO::FETCH_ASSOC);
}

/* FETCH TRAVEL CLEARANCES */
$travel_clearances = [];
if ($tab === 'all' || $tab === 'clearances') {
    $sql_clearances = "SELECT id, name, pap_code, location, travel_date, created_at, status
                       FROM travel_clearances
                       WHERE planner_id = :planner_id AND status = 'pending_planner'
                       ORDER BY created_at DESC";

    $stmt_clearances = $pdo->prepare($sql_clearances);
    $stmt_clearances->bindParam(':planner_id', $logged_in_planner_id, PDO::PARAM_INT);
    $stmt_clearances->execute();
    $travel_clearances = $stmt_clearances->fetchAll(PDO::FETCH_ASSOC);
}

$total_items = count($travel_orders) + count($travel_clearances);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TO - Planner Account</title>
    <link rel="stylesheet" type="text/css" href="../style/email-style.css">
    <link rel="stylesheet" type="text/css" href="../style/pop-up-message.css">
    <link rel= "stylesheet" type= "text/css" href="../style/delete-btn.css">
     <?php include '../includes/header.php';?>
</head>
<body>
    <div class="dashboard-container">
        <p>You have <strong><?php echo $total_items; ?></strong> planner items to review.</p>

        <div class="tabs">
            <a href="index.php?tab=all" class="tab-link <?php echo ($tab === 'all') ? 'active' : ''; ?>">All</a>
            <a href="index.php?tab=orders" class="tab-link <?php echo ($tab === 'orders') ? 'active' : ''; ?>">Travel Orders</a>
            <a href="index.php?tab=clearances" class="tab-link <?php echo ($tab === 'clearances') ? 'active' : ''; ?>">Travel Clearance</a>
        </div>

        <?php if ($tab === 'all' || $tab === 'orders'): ?>
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
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($travel_orders as $order): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                                <td><?php echo htmlspecialchars($order['name']); ?></td>
                                <td><?php echo htmlspecialchars($order['position']); ?></td>
                                <td><?php echo htmlspecialchars($order['destination']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($order['departure_date'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="review_to.php?id=<?php echo $order['id']; ?>" class="btn-review">Review</a>
                                        <button type="submit" class="btn-delete" onclick ="openDeleteOrderModal(<?php echo $order['id']; ?>) ">Delete</button>
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
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($tab === 'all' || $tab === 'clearances'): ?>
            <div class="section-title">Travel Clearance</div>

            <?php if (count($travel_clearances) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Date Submitted</th>
                            <th>Name of Fieldmen</th>
                            <th>PAP Code</th>
                            <th>Location</th>
                            <th>Travel Date</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($travel_clearances as $tc): ?>
                            <tr>
                                <td><?php echo date('M d, Y', strtotime($tc['created_at'])); ?></td>
                                <td><?php echo htmlspecialchars($tc['name']); ?></td>
                                <td><?php echo htmlspecialchars($tc['pap_code']); ?></td>
                                <td><?php echo htmlspecialchars($tc['location']); ?></td>
                                <td><?php echo date('M d, Y', strtotime($tc['travel_date'])); ?></td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="review_tc.php?id=<?php echo $tc['id']; ?>" class="btn-review">Review</a>
                                            <button type="submit" class="btn-delete" onclick ="openDeleteClearanceModal(<?php echo $tc['id'];?>)">Delete</button>
                                        </form>
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
            <?php endif; ?>
        <?php endif; ?>

        <?php if ($tab === 'all' && count($travel_orders) === 0 && count($travel_clearances) === 0): ?>
            <div class="empty-state">
                <h3>All caught up!</h3>
                <p>There are no planner items requiring your attention right now.</p>
            </div>
        <?php endif; ?>
    </div>

   <div id="deleteOrderModal" class="modal">
        <div class="modal-content">
            <h3>Confirm Delete</h3>
            <p>Are you sure you want to delete this travel order?</p>
            <form method="post" id="deleteOrderForm">
                <input type="hidden" name="delete_id" id="deleteOrderId">
                <button class="confirm-btn" type="submit" name="delete_order_btn">Yes, Delete</button>
                <button class="cancel-btn" type="button" onclick="closeDeleteOrderModal()">Cancel</button>
            </form>
        </div>
    </div>

     <div id="deleteClearanceModal" class="modal">
        <div class="modal-content">
            <h3>Confirm Delete</h3>
            <p>Are you sure you want to delete this travel clearance?</p>
            <form method="post" id="deleteClearanceForm">
                <input type="hidden" name="delete_id" id="deleteClearanceId">
                <button class = "confirm-btn" type="submit" name="delete_clearance_btn">Yes, Delete</button>
                <button class = "cancel-btn" type="button" onclick="closeDeleteClearanceModal()">Cancel</button>
            </form>
        </div>
    </div>

    <div class="popup-overlay" id="popupOverlay"></div>
    <div class="popup-message" id="popupMessage">
        <p id="popupText"></p>
        <button onclick="closePopup()">OK</button>
    </div>

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
