<?php
session_start();

try {
    $conn = new PDO("mysql:host=localhost;dbname=to_inventory", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

if (!isset($_SESSION['user_id']) || !isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SESSION['role'] !== 'planner') {
    die("Access denied.");
}

$logged_in_planner_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Error: No Travel Order ID provided.");
}

$order_id = (int) $_GET['id'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['approve_btn'])) {

    $update_sql = "UPDATE travel_orders
                   SET status = 'completed'
                   WHERE id = ? AND status = 'approved'";

    $update_stmt = $conn->prepare($update_sql);

    if ($update_stmt) {
        if ($update_stmt->execute([$order_id])) {
            $_SESSION['success_message'] = 'Travel Order completed successfully!';
            header("Location: index.php?tab=orders");
            exit();
        } else {
            die("Unable to complete this travel order. It may already be processed.");
        }
    } else {
        die("Error updating record: " . $conn->errorInfo()[2]);
    }
}

$sql = "SELECT *
        FROM travel_orders
        WHERE id = ? AND status = 'approved'";

$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->execute([$order_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    die("Error fetching data: " . $conn->errorInfo()[2]);
}

if (!$order) {
    die("Travel Order not found or it has already been processed.");
}

$purposes = json_decode($order['purpose'], true);
$assistants = json_decode($order['assistants'], true);

if (!is_array($purposes)) {
    $purposes = [];
}

if (!is_array($assistants)) {
    $assistants = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Travel Order</title>
    <link rel="stylesheet" type="text/css" href="../style/review_to.css">
</head>
<body>

    <div class="box">
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

        <form method="post" class="action-area">
            <div class="button-group">
                <a href="index.php?tab=orders" class="btn btn-cancel">Go Back</a>
                <button type="submit" name="approve_btn" class="btn btn-approve">
                    Mark as Completed
                </button>
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
        window.location.href = 'index.php?tab=orders';
    }
    </script>

</body>
</html>