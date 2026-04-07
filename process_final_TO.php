<?php
include ('connect.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $name = isset($_POST['Name']) ? trim($_POST['Name']) : '';
    $salary = isset($_POST['Salary']) ? trim($_POST['Salary']) : '';
    $position = isset($_POST['Position']) ? trim($_POST['Position']) : '';
    $division_unit = isset($_POST['division_unit']) ? trim($_POST['division_unit']) : '';
    $departure_date = isset($_POST['Departure_Date']) ? $_POST['Departure_Date'] : '';
    $official_station = isset($_POST['Official_Station']) ? trim($_POST['Official_Station']) : '';
    $destination = isset($_POST['Destination']) ? trim($_POST['Destination']) : '';
    $arrival_date = isset($_POST['Arrival_Date']) ? $_POST['Arrival_Date'] : '';
    $per_diems = isset($_POST['Per_Diems']) ? trim($_POST['Per_Diems']) : '';
    $appropriation = isset($_POST['Appropriation']) ? trim($_POST['Appropriation']) : '';
    $remarks = isset($_POST['Remarks']) ? trim($_POST['Remarks']) : '';
    $officer_id = isset($_POST['Officer']) ? trim($_POST['Officer']) : '';

    $purposes_json = isset($_POST['Purpose']) ? json_encode($_POST['Purpose']) : '[]';
    $assistants_json = isset($_POST['Assistants']) ? json_encode($_POST['Assistants']) : '[]';

    if (empty($officer_id)) {
        die("Please select a Division Chief.");
    }

    $signature_path = '';

    if (isset($_FILES['e_signature']) && $_FILES['e_signature']['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'uploads/signatures/';

        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0777, true);
        }

        $file_tmp_path = $_FILES['e_signature']['tmp_name'];
        $file_name = $_FILES['e_signature']['name'];
        $file_extension = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        $allowed_exts = array('jpg', 'jpeg', 'png');

        if (in_array($file_extension, $allowed_exts)) {
            $new_file_name = uniqid('sig_', true) . '.' . $file_extension;
            $destination_path = $upload_dir . $new_file_name;

            if (move_uploaded_file($file_tmp_path, $destination_path)) {
                $signature_path = $destination_path;
            } else {
                die("Error moving the uploaded file.");
            }
        } else {
            die("Invalid file type. Only JPG, JPEG, and PNG are allowed.");
        }
    } else {
        die("Please upload an e-signature.");
    }

    $sql = "INSERT INTO travel_orders (
                name, salary, position, division_unit, departure_date,
                official_station, destination, arrival_date, purpose,
                per_diems, assistants, appropriation, remarks,
                officer_id, applicant_signature, status
            ) VALUES (
                ?, ?, ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, ?, ?,
                ?, ?, 'pending_do'
            )";

    $stmt = $conn->prepare($sql);

    if ($stmt) {
        if ($stmt->execute([
            $name,
            $salary,
            $position,
            $division_unit,
            $departure_date,
            $official_station,
            $destination,
            $arrival_date,
            $purposes_json,
            $per_diems,
            $assistants_json,
            $appropriation,
            $remarks,
            $officer_id,
            $signature_path
        ])) {
            echo '
            <!DOCTYPE html>
            <html lang="en">
            <head>
                <meta charset="utf-8">
                <meta name="viewport" content="width=device-width, initial-scale=1.0">
                <title>Success!</title>
                <style>
                    body {
                        font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
                        background-color: #f4f7f6;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        height: 100vh;
                        margin: 0;
                    }
                    .modal-card {
                        background-color: #ffffff;
                        padding: 40px;
                        border-radius: 12px;
                        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
                        text-align: center;
                        max-width: 450px;
                        width: 90%;
                    }
                    .success-icon {
                        background-color: #2ecc71;
                        color: white;
                        width: 60px;
                        height: 60px;
                        border-radius: 50%;
                        display: flex;
                        justify-content: center;
                        align-items: center;
                        font-size: 30px;
                        margin: 0 auto 20px auto;
                    }
                    h2 {
                        color: #2c3e50;
                        margin-top: 0;
                        margin-bottom: 10px;
                    }
                    p {
                        color: #555;
                        margin-bottom: 30px;
                        line-height: 1.5;
                    }
                    .btn {
                        background-color: #3498db;
                        color: white;
                        text-decoration: none;
                        padding: 12px 24px;
                        border-radius: 6px;
                        font-weight: 600;
                        display: inline-block;
                        transition: background 0.2s;
                    }
                    .btn:hover {
                        background-color: #2980b9;
                    }
                </style>
            </head>
            <body>
                <div class="modal-card">
                    <div class="success-icon">✓</div>
                    <h2>Success!</h2>
                    <p>Your travel order was successfully submitted and is now awaiting Division Chief approval.</p>
                    <a href="../TO_MGB/index.php" class="btn">Return to Home</a>
                    <a href="../TO_MGB/TC.php" class="btn">Clearance</a>
                </div>
            </body>
            </html>
            ';
        } else {
            die("Error saving to database: " . $stmt->errorInfo()[2]);
        }
    } else {
        die("Error preparing statement: " . $conn->errorInfo()[2]);
    }
}
?>