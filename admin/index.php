<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    die("Access denied.");
}

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

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$sql = "SELECT id, name, position, destination, departure_date, created_at
        FROM travel_orders
        WHERE status = 'pending_rd'
        ORDER BY created_at DESC";

$stmt = $pdo->prepare($sql);
$stmt->execute();
$pending_orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TO - Regional Director Account</title>
    <link rel="stylesheet" type="text/css" href="../style/email-style.css">
    <link rel="stylesheet" type="text/css" href="../style/pop-up-message.css">
     <?php include '../includes/header.php';?>
</head>
<body>

    <div class="dashboard-container">
        
        <p>You have <strong><?php echo count($pending_orders); ?></strong> travel orders waiting for Regional Director approval.</p>

        <?php if (count($pending_orders) > 0): ?>
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
                    <?php foreach ($pending_orders as $order): ?>
                        <tr>
                            <td><?php echo date('M d, Y', strtotime($order['created_at'])); ?></td>
                            <td><?php echo htmlspecialchars($order['name']); ?></td>
                            <td><?php echo htmlspecialchars($order['position']); ?></td>
                            <td><?php echo htmlspecialchars($order['destination']); ?></td>
                            <td><?php echo date('M d, Y', strtotime($order['departure_date'])); ?></td>
                            <td>
                                <a href="review_to.php?id=<?php echo $order['id']; ?>" class="btn-review">Review & Approve</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else: ?>
            <div class="empty-state">
                <h3>All caught up!</h3>
                <p>There are no pending travel orders requiring your attention right now.</p>
            </div>
        <?php endif; ?>
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