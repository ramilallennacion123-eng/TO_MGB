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

if ($_SESSION['role'] !== 'chief') {
    die("Access denied.");
}

$logged_in_do_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Error: No Travel Order ID provided.");
}

$order_id = (int) $_GET['id'];
$signature_sql = "SELECT user_signature FROM users WHERE id = ?";
$signature_stmt = $conn->prepare($signature_sql);
$signature_stmt->execute([$logged_in_do_id]);
$signature_data = $signature_stmt->fetch(PDO::FETCH_ASSOC);
$do_saved_signature = $signature_data['user_signature'] ?? '';

if (empty($do_saved_signature)) {
    die('Your signature file was not found. Please upload your signature first.');
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['approve_btn'])) {

    $update_sql = "UPDATE travel_orders 
                   SET status = 'pending_rd', do_signature = ? 
                   WHERE id = ? AND officer_id = ? AND status = 'pending_do'";

    $update_stmt = $conn->prepare($update_sql);

    if ($update_stmt) {
        if ($update_stmt->execute([$do_saved_signature, $order_id, $logged_in_do_id])) {
            $_SESSION['success_message'] = 'Travel Order sent successfully!';
            header("Location: division_officer.php");
            exit();
        } else {
            die("Unable to approve this travel order. It may not belong to your account, may already be processed, or officer_id does not match your login ID.");
        }
    } else {
        die("Error updating record: " . $conn->errorInfo()[2]);
    }
}

$sql = "SELECT * 
        FROM travel_orders 
        WHERE id = ? AND officer_id = ? AND status = 'pending_do'";

$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->execute([$order_id, $logged_in_do_id]);
    $order = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    die("Error fetching data: " . $conn->errorInfo()[2]);
}

if (!$order) {
    die("Travel Order not found, does not belong to your account, or it has already been processed.");
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
                <p>No signature uploaded.</p>
            <?php endif; ?>
        </div>

        <form method="post" class="action-area">
            <div class="auth-section">
                <p>By clicking approve, your digital signature will be attached:</p>
                <img src="<?php echo htmlspecialchars($do_saved_signature); ?>" alt="Your Signature">
            </div>

            <div class="button-group">
                <a href="division_officer.php" class="btn btn-cancel">Go Back</a>
                <button type="submit" name="approve_btn" class="btn btn-approve">
                    Sign, Approve &amp; Forward to Regional Director
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
        window.location.href = 'division_officer.php';
    }
    </script>

</body>
</html>