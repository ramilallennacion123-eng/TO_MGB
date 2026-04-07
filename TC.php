<?php
include ('connect.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Travel Clearance</title>
    <link rel="stylesheet" type="text/css" href="style/TC.css">
</head>
<body>
    <div class="box">
        <div class="top-buttons">
            <a href="../TO_MGB/TO.php" class="nav-btn">Previous</a>
            <a href="./index.php" class="nav-btn">Home</a>
        </div>

        <h1 class="card_title">Travel Clearance</h1>

        <form method="post" action="process_TC.php" id="travelClearanceForm" autocomplete="off">
            <div class="form-container">
                <div class="input-group full-width">
                    <label for="Name">Name of Fieldmen</label>
                    <input type="text" id="Name" name="Name" required>
                </div>
                <div class="input-group full-width" id="TCpurpose-container">
                    <label>Purpose of Travel</label>
                    <input type="text" name="Purpose[]">
                    <div class="button-group">
                        <button type="button" onclick="TCaddPurpose()">+ Add Purpose</button>
                    </div>
                </div>

                <div class="input-group full-width">
                    <label for="location">Location</label>
                    <input type="text" id="location" name="location" required>
                </div>

                <div class="input-group full-width">
                    <label for="travel_date">Travel Date</label>
                    <input type="text" id="travel_date" name="travel_date" required>
                </div>

                <div class="submit-row">
                    <button type="submit" name="submit_tc">Submit</button>
                </div>
            </div>
        </form>
    </div>

    <script>
        let purposeCount = 0;

        function TCaddPurpose() {
            if (purposeCount >= 1) return;
            
            const container = document.getElementById("TCpurpose-container");
            const wrapper = document.createElement("div");
            wrapper.style.display = "flex";
            wrapper.style.gap = "8px";
            wrapper.style.marginTop = "8px";
            
            const input = document.createElement("input");
            input.type = "text";
            input.name = "Purpose[]";
            input.required = true;
            input.style.flex = "1";
            
            const deleteBtn = document.createElement("button");
            deleteBtn.type = "button";
            deleteBtn.textContent = "Delete";
            deleteBtn.onclick = function() {
                wrapper.remove();
                purposeCount--;
                document.querySelector(".button-group").style.display = "block";
            };
            
            wrapper.appendChild(input);
            wrapper.appendChild(deleteBtn);
            container.insertBefore(wrapper, container.querySelector(".button-group"));
            purposeCount++;
            
            if (purposeCount >= 1) {
                document.querySelector(".button-group").style.display = "none";
            }
        }
    </script>
</body>
</html>