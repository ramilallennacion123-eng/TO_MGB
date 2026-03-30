<?php
session_start();

$conn = mysqli_connect("localhost", "root", "", "to_inventory");

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

if (!isset($_SESSION['user_id']) || !isset($_SESSION['username']) || !isset($_SESSION['role'])) {
    header("Location: ../login.php");
    exit();
}

if ($_SESSION['role'] !== 'admin') {
    die("Access denied.");
}

$logged_in_admin_id = $_SESSION['user_id'];
$username = $_SESSION['username'];

if (!isset($_GET['id']) || empty($_GET['id'])) {
    die("Error: No Travel Order ID provided.");
}

$order_id = (int) $_GET['id'];

$signature_sql = "SELECT user_signature FROM users WHERE id = ?";
$signature_stmt = mysqli_prepare($conn, $signature_sql);
mysqli_stmt_bind_param($signature_stmt, "i", $logged_in_admin_id);
mysqli_stmt_execute($signature_stmt);
$signature_result = mysqli_stmt_get_result($signature_stmt);
$signature_data = mysqli_fetch_assoc($signature_result);
mysqli_stmt_close($signature_stmt);

$admin_saved_signature = $signature_data['user_signature'] ?? '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['approve_btn'])) {

    $update_sql = "UPDATE travel_orders 
                   SET status = 'approved', rd_signature = ? 
                   WHERE id = ? AND status = 'pending_rd'";

    $update_stmt = mysqli_prepare($conn, $update_sql);

    if ($update_stmt) {
        mysqli_stmt_bind_param($update_stmt, "si", $admin_saved_signature, $order_id);
        mysqli_stmt_execute($update_stmt);

        if (mysqli_stmt_affected_rows($update_stmt) > 0) {
            mysqli_stmt_close($update_stmt);
            $_SESSION['success_message'] = 'Travel Order approved successfully!';
            header("Location: index.php");
            exit();
        } else {
            mysqli_stmt_close($update_stmt);
            die("Unable to approve this travel order. It may already be processed.");
        }
    } else {
        die("Error updating record: " . mysqli_error($conn));
    }
}

$sql = "SELECT * 
        FROM travel_orders 
        WHERE id = ? AND status = 'pending_rd'";

$stmt = mysqli_prepare($conn, $sql);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "i", $order_id);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $order = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
} else {
    die("Error fetching data: " . mysqli_error($conn));
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

mysqli_close($conn);
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

        <div class="signature-box">
            <strong>Chief Signature</strong><br>
            <?php if (!empty($order['do_signature'])): ?>
                <img src="<?php echo htmlspecialchars($order['do_signature']); ?>" alt="Chief Signature">
            <?php else: ?>
                <p>No chief signature attached.</p>
            <?php endif; ?>
        </div>

        <form method="post" class="action-area">
            <div class="auth-section">
                <p>By clicking approve, your digital signature will be attached:</p>
                <img src="<?php echo htmlspecialchars($admin_saved_signature); ?>" alt="Regional Director Signature">
            </div>

            <div class="button-group">
                <a href="index.php" class="btn btn-cancel">Go Back</a>
                <button type="submit" name="approve_btn" class="btn btn-approve">
                    Sign &amp; Final Approve
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
        window.location.href = 'index.php';
    }
    </script>

</body>
</html>