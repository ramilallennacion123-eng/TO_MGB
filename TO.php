<?php

include ('connect.php');

$sql = "SELECT id, name, role FROM users WHERE role = 'chief' ";
$all_chief = $conn->query($sql);


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travel Order</title>
    <link rel="stylesheet" type="text/css" href="style/TO.css">
</head>
<body>

    <div class="box">
        <div class="top-buttons">
            <a href="../TO_MGB/index.php" class="nav-btn">Return</a>
            <a href="../TO_MGB/TC.php" class="nav-btn">Travel Clearance</a>
        </div>

        <h1 class="card_title">Travel Order</h1>

        <form method="post" id="travelForm" enctype="multipart/form-data">
            <div class="form-container">

                <div class="input-group">
                    <label for="Name">Name</label>
                    <input type="text" id="Name" name="Name" >
                </div>

                <div class="input-group">
                    <label for="Salary">Salary</label>
                    <input type="number" id="Salary" name="Salary" >
                </div>

                <div class="input-group">
                    <label for="Position">Position</label>
                    <input type="text" id="Position" name="Position" >
                </div>

                <div class="input-group">
                    <label for="division_unit">Div/Sec/Unit</label>
                    <input type="text" id="division_unit" name="division_unit" >
                </div>

                <div class="input-group">
                    <label for="Departure_Date">Departure Date</label>
                    <input type="date" id="Departure_Date" name="Departure_Date" >
                </div>

                <div class="input-group">
                    <label for="Official_Station">Official Station</label>
                    <input type="text" id="Official_Station" name="Official_Station" >
                </div>

                <div class="input-group">
                    <label for="Destination">Destination</label>
                    <input type="text" id="Destination" name="Destination" >
                </div>

                <div class="input-group">
                    <label for="Arrival_Date">Arrival Date</label>
                    <input type="date" id="Arrival_Date" name="Arrival_Date" >
                </div>

                <div class="input-group full-width" id="purpose-container">
                    <label>Purpose of Travel</label>
                    <input type="text" name="Purpose[]" >
                    <div class="button-group">
                        <button type="button" onclick="addPurpose()">+ Add Purpose</button>
                    </div>
                </div>

                <div class="input-group">
                    <label for="Per_Diems">Per Diems/Expenses Allowed</label>
                    <input type="text" id="Per_Diems" name="Per_Diems">
                </div>

                <div class="input-group" id="assistant-container">
                    <label>Assistants/Laborers Allowed</label>
                    <input type="text" name="Assistants[]">
                    <div class="button-group">
                        <button type="button" onclick="addAssistant()">+ Add Assistant</button>
                    </div>
                </div>

                <div class="input-group full-width">
                    <label for="Appropriation">Appropriation to which travel should be charged</label>
                    <input type="text" id="Appropriation" name="Appropriation">
                </div>

                <div class="input-group full-width">
                    <label for="Remarks">Remarks or Special Instructions</label>
                    <input type="text" id="Remarks" name="Remarks">
                </div>

                <div class="input-group full-width">
                    <label for="Officer">Division Chief</label>
                    <select name="Officer" id="Officer" required>
                        <option value="">Choose Division Chief</option>
                        <?php 
                while ($chief = $all_chief->fetch(PDO::FETCH_ASSOC)):; 
            ?>
                <option value="<?php echo $chief["id"];
                ?>">
                    <?php echo $chief["name"];
                    ?>
                </option>
            <?php 
                endwhile; 
            ?>
                    </select>
                </div>

                <div class="input-group full-width">
                    <label for="e_signature">Attach Signature Here</label>
                    <input type="file" name="e_signature" id="e_signature" accept="image/*" required>
                </div>
                
                <div class="submit-row">
                    <button type="submit" name="process_final_TO" id= "travelOrderData" value="Submit" formaction="process_final_TO.php">Submit</button>
                </div>
            </div>
        </form>  
    </div>     
</body>
</html>
<script src="function.js"></script>