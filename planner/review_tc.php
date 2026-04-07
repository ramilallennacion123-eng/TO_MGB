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
    die("Error: No Travel Clearance ID provided.");
}

$clearance_id = (int) $_GET['id'];

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['approve_btn'])) {

    $update_sql = "UPDATE travel_clearances
                   SET status = 'approved_planner'
                   WHERE id = ? AND planner_id = ? AND status = 'pending_planner'";

    $update_stmt = $conn->prepare($update_sql);

    if ($update_stmt) {
        if ($update_stmt->execute([$clearance_id, $logged_in_planner_id])) {
            $_SESSION['success_message'] = 'Travel Clearance approved successfully!';
            header("Location: index.php");
            exit();
            
        } else {
            die("Unable to approve this travel clearance. It may not belong to your account or it may already be processed.");
        }
    } else {
        die("Error updating record: " . $conn->errorInfo()[2]);
    }
}

$sql = "SELECT *
        FROM travel_clearances
        WHERE id = ? AND planner_id = ? AND status = 'pending_planner'";

$stmt = $conn->prepare($sql);

if ($stmt) {
    $stmt->execute([$clearance_id, $logged_in_planner_id]);
    $clearance = $stmt->fetch(PDO::FETCH_ASSOC);
} else {
    die("Error fetching data: " . $conn->errorInfo()[2]);
}

if (!$clearance) {
    die("Travel Clearance not found, does not belong to your account, or it has already been processed.");
}

$purposes = json_decode($clearance['purpose'], true);

if (!is_array($purposes)) {
    $purposes = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Travel Clearance</title>
    <link rel="stylesheet" type="text/css" href="../style/review_tc.css">>
</head>
<body>

    <div class="box">
        <h1>Review Travel Clearance</h1>

        <div class="detail-grid">
            <div class="detail-item full-width"><strong>Name of Fieldmen</strong> <?php echo htmlspecialchars($clearance['name']); ?></div>
            <div class="detail-item full-width"><strong>Location</strong> <?php echo htmlspecialchars($clearance['location']); ?></div>
            <div class="detail-item full-width"><strong>Travel Date</strong> <?php echo htmlspecialchars($clearance['travel_date']); ?></div>

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
        </div>

        <form method="post" class="action-area">
            <div class="button-group">
                <a href="index.php" class="btn btn-cancel">Go Back</a>
                <button type="submit" name="approve_btn" class="btn btn-approve">
                    Approve Travel Clearance
                </button>
                <div>
                    <a class="btn pdf-btn" href="TC_pdf.php?id=<?php echo $clearance['id']; ?>" class="btn">Download PDF</a>
                </div>
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