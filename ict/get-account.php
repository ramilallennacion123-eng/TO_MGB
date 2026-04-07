<?php
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>User Accounts</title>
  <link rel="stylesheet" type="text/css" href="../style/ict-style.css">
  <style>
    body {
      font-family: Arial, sans-serif;
      background: #f4f4f4;
      padding: 20px;
    }
    .container {
      max-width: 1000px;
      margin: 0 auto;
      background: #fff;
      padding: 30px;
      border-radius: 8px;
      box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    }
    h2 {
      color: #333;
      margin-bottom: 20px;
      text-align: center;
    }
    table {
      width: 100%;
      border-collapse: collapse;
      margin-top: 20px;
    }
    thead {
      background: #4CAF50;
      color: white;
    }
    th, td {
      padding: 12px;
      text-align: center;
      border: 1px solid #ddd;
    }
    th {
      font-weight: bold;
      text-transform: uppercase;
      font-size: 14px;
    }
    tbody tr:nth-child(even) {
      background: #f9f9f9;
    }
    tbody tr:hover {
      background: #f1f1f1;
    }
    .btn {
      background: #f44336;
      color: white;
      border: none;
      padding: 6px 12px;
      border-radius: 4px;
      cursor: pointer;
      font-size: 12px;
    }
    .btn:hover {
      background: #d32f2f;
    }
    .back-button{
      background-color:#4CAF50;
      color: white;
      padding: 10px 15px;
      cursor: pointer;
      border-radius: 5px;
      border: none;

    }
    a{
      text-decoration: none;
    }
    .btn-create {
      background-color: #4CAF50;
      color: white;
      padding: 10px 15px;
      cursor: pointer;
      border-radius: 5px;
      border: none;
      float: right;
    }
  </style>
</head>
<body>
  <div class="container">
    <a href="index.php"><button class="back-button">BACK</button></a>
        <button class="btn-create" onclick="document.getElementById('createAccModal').style.display='block'">Create Account</button>
    <h2>User Accounts</h2>
    <table>
      <thead>
        <tr>
          <th>ID</th>
          <th>NAME</th>
          <th>ROLE</th>
          <th>POSITION</th>
          <th>USERNAME</th>
          <th>ACTION</th>
        </tr>
      </thead>
      <tbody>
        <?php require('./display_users.php'); ?>
      </tbody>
    </table>
  </div>

  </div> <div id="createAccModal" class="modal">
        <div class="modal-content">
            <h3>Create Account</h3>
            <form method="post" id="createAccForm">
                <div>Select Role</div>
                <select name="role" required>
                    <option value="">Select Role</option>
                    <option value="ict">ICT</option>
                    <option value="planner">Planner</option>
                    <option value="rd">RD</option>
                    <option value="chief">Chief</option>
                </select>
                <div>Full Name</div>
                <input type="text" name="name" placeholder="Ex. Juan Dela Cruz" required>
                <div>Username</div>
                <input type="text" name="username" placeholder="Ex. juan_123" required>
                <div>Password</div>
                <input type="password" name="password" placeholder="********" required>
                <div class="modal-buttons">
                    <button type="submit" class="submit-btn">Create</button>
                    <button type="button" class="cancel-btn" onclick="document.getElementById('createAccModal').style.display='none'">Cancel</button>
                </div>
            </form>
        </div>
    </div>
    <div class="popup-overlay" id="popupOverlay"></div>
    <div class="popup-message" id="popupMessage">
        <p id="popupText"></p>
        <button onclick="closePopup()">OK</button>
    </div>
   <script src="ict-script.js"></script>
</body>
</html>