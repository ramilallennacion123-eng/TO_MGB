<?php
include ('connect.php');    

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_tc'])) {
    $name = isset($_POST['Name']) ? trim($_POST['Name']) : '';

    $location = isset($_POST['location']) ? trim($_POST['location']) : '';
    $travel_date = isset($_POST['travel_date']) ? $_POST['travel_date'] : '';
    
    $planner_query = $conn->query("SELECT id FROM users WHERE role='planner' LIMIT 1");
    $planner_row = $planner_query->fetch(PDO::FETCH_ASSOC);
    $planner_id = $planner_row ? $planner_row['id'] : null;
    
    $purposes_json = isset($_POST['Purpose']) ? json_encode($_POST['Purpose']) : '[]';

    if (empty($name) || empty($location) || empty($travel_date)) {
        die("Please complete all required fields.");
    }

    $sql = "INSERT INTO travel_clearances
            (name, purpose, location, travel_date, planner_id, status)
            VALUES (?, ?, ?, ?, ?, 'pending_planner')";

    $stmt = $conn->prepare($sql);

    if ($stmt) {
        if ($stmt->execute([
            $name,
            $purposes_json,
            $location,
            $travel_date,
            $planner_id
        ])) {
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
            die("Error saving travel clearance: " . $stmt->errorInfo()[2]);
        }
    } else {
        die("Error preparing statement: " . $conn->errorInfo()[2]);
    }
}
?>
