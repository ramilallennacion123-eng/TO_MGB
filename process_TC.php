<?php
session_start();

$conn = mysqli_connect("localhost", "root", "", "to_inventory");

if (!$conn) {
    die("Database connection failed: " . mysqli_connect_error());
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_tc'])) {
    $name = isset($_POST['Name']) ? trim($_POST['Name']) : '';
    $pap_code = isset($_POST['pap_code']) ? trim($_POST['pap_code']) : '';
    $location = isset($_POST['location']) ? trim($_POST['location']) : '';
    $travel_date = isset($_POST['travel_date']) ? $_POST['travel_date'] : '';
    
    $planner_query = mysqli_query($conn, "SELECT id FROM users WHERE role='planner' LIMIT 1");
    $planner_row = mysqli_fetch_assoc($planner_query);
    $planner_id = $planner_row ? $planner_row['id'] : null;
    
    $purposes_json = isset($_POST['Purpose']) ? json_encode($_POST['Purpose']) : '[]';

    if (empty($name) || empty($pap_code) || empty($location) || empty($travel_date)) {
        die("Please complete all required fields.");
    }

    $sql = "INSERT INTO travel_clearances
            (name, pap_code, purpose, location, travel_date, planner_id, status)
            VALUES (?, ?, ?, ?, ?, ?, 'pending_planner')";

    $stmt = mysqli_prepare($conn, $sql);

    if ($stmt) {
        mysqli_stmt_bind_param(
            $stmt,
            "sssssi",
            $name,
            $pap_code,
            $purposes_json,
            $location,
            $travel_date,
            $planner_id
        );

        if (mysqli_stmt_execute($stmt)) {
            echo ' <!DOCTYPE html>
                    <html lang="en">
                    <head>
                        <meta charset="UTF-8">
                        <meta name="viewport" content="width=device-width, initial-scale=1.0">
                        <title>Travel Clearance Processed</title>
                        <style>
                            body{
                                font-family: "Segoe UI", Tahoma, Geneva, Verdana, sans-serif;
                                background-color: #f4f7f6;
                                display:flex;
                                justify-content: center;
                                align-items:center;
                                height: 100vh;
                                margin:0;
                            }
                            .modal-card {
                                max-width: 450px;
                                width:90%;
                                padding: 40px;
                                border-radius: 10px;
                                box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
                                background-color: #ffffff;
                                text-align: center;
                                }
                            .success-icon {
                                font-size: 40px;
                                width:60px;
                                height:60px;
                                border-radius:50%;
                                background-color: #2ecc71;
                                display:flex;
                                justify-content: center;
                                align-items:center;
                                margin: 0 auto 20px auto;
                                }
                            .btn {
                                text-decoration:none;
                                color:white;
                                background-color:#3498db;
                                padding: 12px 24px;
                                border-radius: 6px;
                                font-weight: 600;
                                display:inline-block;
                                transition-background: 0.2s;
                            }
                            .btn:hover{
                                background-color: #2980b9;
                            }
                            p {
                                color: #555;
                                margin-bottom: 30px;
                                line-height: 1.5;
                            }
                            h3 {
                                color: #2c3e50;
                                margin-top: 0;
                                margin-bottom: 10px;
                            }

                        </style>
                    </head>
                    <body>
                        <div class ="modal-card">
                            <div class = "success-icon">✓</div>
                            <h3>Travel Clearance Submitted Successfully!</h3>
                            <p>Travel Clearance is sent to Planner and now in process.<p>
                            <a href = "index.php" class = "btn">Return home</a>
                            <a href = "TO.php"class ="btn">Travel Order</a>
                        </div>
                        
                    </body>
                    </html>';
        } else {
            die("Error saving travel clearance: " . mysqli_stmt_error($stmt));
        }

        mysqli_stmt_close($stmt);
    } else {
        die("Error preparing statement: " . mysqli_error($conn));
    }

    mysqli_close($conn);
}
?>
