<?php 
session_start();
if($_SESSION['role'] !== 'ict'){
  die('Access Denied!');
}

try {
    $conn = new PDO("mysql:host=localhost;dbname=to_inventory", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

$order_id = (int)$_GET['id'];

if($_SERVER['REQUEST_METHOD'] === 'POST'){
  $applicant_x = $_POST['applicant_x'];
  $applicant_y = $_POST['applicant_y'];
  $do_x = $_POST['do_x'];
  $do_y = $_POST['do_y'];
  $rd_x = $_POST['rd_x'];
  $rd_y = $_POST['rd_y'];
  
  $sql = "UPDATE travel_orders SET 
          applicant_sig_x = ?, applicant_sig_y = ?,
          do_sig_x = ?, do_sig_y = ?,
          rd_sig_x = ?, rd_sig_y = ?
          WHERE id = ?";
  $stmt = $conn->prepare($sql);
  $stmt->execute([$applicant_x, $applicant_y, $do_x, $do_y, $rd_x, $rd_y, $order_id]);
  
  header("Location: TO_pdf.php?id=" . $order_id);
  exit;
}

$stmt = $conn->prepare("SELECT * FROM travel_orders WHERE id = ?");
$stmt->execute([$order_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

$name = $order['name'] ?? '';
$salary = $order['salary'] ?? '';
$position = $order['position'] ?? '';
$division_unit = $order['division_unit'] ?? '';
$departure_date = $order['departure_date'] ?? '';
$official_station = $order['official_station'] ?? '';
$destination = $order['destination'] ?? '';
$arrival_date = $order['arrival_date'] ?? '';
$purposes_array = json_decode($order['purpose'], true);
$per_diems = $order['per_diems'] ?? '';
$assistants_array = json_decode($order['assistants'], true);
$appropriation = $order['appropriation'] ?? '';
$remarks = $order['remarks'] ?? '';

$officer_id = $order['officer_id'];
$officer = [];
if($officer_id){
    $stmt = $conn->prepare("SELECT name, position FROM users WHERE id = ? AND role = 'chief'");
    $stmt->execute([$officer_id]);
    $officer = $stmt->fetch(PDO::FETCH_ASSOC);
}

$stmt = $conn->prepare("SELECT name, position FROM users WHERE role = 'rd' LIMIT 1");
$stmt->execute();
$rd = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html>
<head>
  <meta charset="UTF-8">
  <title>Adjust Signatures</title>
  <link rel="stylesheet" type = "text/css" href="../style/adjust-sig-page.css">
</head>
<body>
  <div class="container">
    <h2>Adjust Signature Positions</h2>
    <div class="info">Drag ONLY the signature images to position them. Click "Save & Generate PDF" when done.</div>
    
    <div class="preview-area" id="previewArea">
      <div class="pdf-content">
        <p style="text-align:center; margin-bottom:0;">Republic of the Philippines</p>
        <p style="text-align:center; margin-top:0px;margin-bottom:0px;">Department of Environment and Natural Resources</p>
        <p style="font-weight: bold; text-align:center; margin-top:0px; margin-bottom:0;">MINES AND GEOSCIENCES BUREAU-V</p>
        <p style="text-align:center; margin-top:0;">Regional Center, Rawis, Legazpi City</p>
        <p style="text-align:center; margin-bottom:0;">TRAVEL ORDER</p>
        <p style="text-align:center; margin-top:0;">(No._________________)</p>
        <br>
        <table class="form-table">
          <tr>
            <td style="width: 50%;">
              <table class="inner-row">
                <tr>
                  <td class="label-cell">Name:</td>
                  <td class="line-cell"><b><?php echo strtoupper($name); ?></b></td>
                </tr>
              </table>
            </td>
            <td style="width: 50%;">
              <table class="inner-row">
                <tr>
                  <td class="label-cell">Salary:</td>
                  <td class="line-cell"><?php echo htmlspecialchars($salary); ?></td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td>
              <table class="inner-row">
                <tr>
                  <td class="label-cell">Position:</td>
                  <td class="line-cell"><?php echo htmlspecialchars($position); ?></td>
                </tr>
              </table>
            </td>
            <td>
              <table class="inner-row">
                <tr>
                  <td class="label-cell">Div/Sec/Unit:</td>
                  <td class="line-cell"><?php echo htmlspecialchars($division_unit); ?></td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td>
              <table class="inner-row">
                <tr>
                  <td class="label-cell">Departure Date:</td>
                  <td class="line-cell"><?php echo date('F j, Y', strtotime($departure_date)); ?></td>
                </tr>
              </table>
            </td>
            <td>
              <table class="inner-row">
                <tr>
                  <td class="label-cell">Official Station:</td>
                  <td class="line-cell"><?php echo htmlspecialchars($official_station); ?></td>
                </tr>
              </table>
            </td>
          </tr>
          <tr>
            <td>
              <table class="inner-row">
                <tr>
                  <td class="label-cell">Destination:</td>
                  <td class="line-cell"><?php echo htmlspecialchars($destination); ?></td>
                </tr>
              </table>
            </td>
            <td>
              <table class="inner-row">
                <tr>
                  <td class="label-cell">Arrival Date:</td>
                  <td class="line-cell"><?php echo date('F j, Y', strtotime($arrival_date)); ?></td>
                </tr>
              </table>
            </td>
          </tr>
        </table>
        <br>
        <table style="width: 100%; border-collapse: collapse; font-size:13px">
          <tr>
            <td style="font-size: 11pt; width: 1%; white-space: nowrap; vertical-align: top; padding-right: 10px;">Purpose of Travel:</td>
            <td style="vertical-align: top;">
              <ol style="margin: 0; padding-left: 15px;">
                <?php if(!empty($purposes_array)): foreach($purposes_array as $p): ?>
                  <li style="font-size: 11pt; margin-bottom: 4px; text-align: justify;"><?php echo htmlspecialchars($p); ?></li>
                <?php endforeach; endif; ?>
              </ol>
            </td>
          </tr>
        </table>
        <br>
        <table style="width: 75%; border-collapse: collapse; margin-bottom: 2px; font-size:13px;">
          <tr>
            <td class="label-cell">Per Diems/Expenses Allowed:</td>
            <td class="line-cell"><?php echo htmlspecialchars($per_diems); ?></td>
          </tr>
        </table>
        <table style="width: 75%; border-collapse: collapse; margin-bottom: 2px;font-size:13px;">
          <tr>
            <td class="label-cell">Assistants or Laborers Allowed:</td>
            <td class="line-cell"><?php if(!empty($assistants_array)) echo implode(', ', array_map('htmlspecialchars', $assistants_array)); ?></td>
          </tr>
        </table>
        <table style="width: 75%; border-collapse: collapse; margin-bottom: 2px;font-size:13px;">
          <tr>
            <td class="label-cell">Appropriations to which travel should be charged:</td>
            <td class="line-cell"><?php echo htmlspecialchars($appropriation); ?></td>
          </tr>
        </table>
        <table style="width: 100%; border-collapse: collapse;font-size:13px;">
          <tr>
            <td class="label-cell">Remarks or special instructions:</td>
            <td class="line-cell"><?php echo htmlspecialchars($remarks); ?></td>
          </tr>
          <tr>
            <td class="label-cell"></td>
            <td class="line-cell" style="padding-top: 18px;"></td>
          </tr>
        </table>
        
        <p style="font-weight: bold; margin-top: 10px; margin-bottom: 5px;">Certifications:</p>
        <div style="text-indent: 3em; font-size:11pt; text-align: justify;">This is to certify that the travel is necessary and is connected with the function of the official/employee of this Div/Sec/Unit.</div>
        <table style="width: 100%; border-collapse: collapse; margin-top: 10px;">
          <tr>
            <td style="width: 50%; vertical-align: bottom;"><p style="margin-bottom: 5px;">Recommending Approval:</p></td>
            <td style="width: 50%; vertical-align: bottom;"><p style="font-size:11pt; margin-bottom: 5px;">Approved:</p></td>
          </tr>
        </table>
        
        <div style="border-bottom: 1px solid black; width: 100%; padding-bottom: 10px; margin-top: 0px; position: relative; min-height: 50px;">
          <table style="width: 100%; border-collapse: collapse;">
            <tr>
              <td style="width: 50%; vertical-align: bottom;">
                <p style="font-size:13px; font-weight: bold; margin: 40px 0 0 0; text-align: left;"><u><?php echo strtoupper($officer['name'] ?? 'CHIEF NAME'); ?></u></p>
                <p style="font-size:11pt; margin: 0; text-align: left;"><?php echo $officer['position'] ?? 'Chief Position'; ?></p>
              </td>
              <td style="width: 50%; vertical-align: bottom;">
                <p style="font-size:13px; font-weight: bold; margin: 40px 0 0 0; text-align: left;"><u><?php echo strtoupper($rd['name'] ?? 'RD NAME'); ?></u></p>
                <p style="font-size:11pt; margin: 0; text-align: left;"><?php echo $rd['position'] ?? 'Regional Director'; ?></p>
              </td>
            </tr>
          </table>
        </div>
        
        <p style="text-align:center; font-weight: bold; margin-top: 15px;">AUTHORIZATION</p>
        <p style="text-indent: 2em; font-size:11pt; text-align:justify;">I hereby authorize the Accountant to deduct the corresponding amount of the unliquidated cash advance from my succeding for my failure to liquidate this travel within twenty(20) days upon return to my permanent official station pursuant to Commission on Audit(COA) Circular No. 2012-004 dated November 28, 2012.</p>
        
        <div style="text-align: center; margin-top: 40px; position: relative; min-height: 50px;">
          <p style="text-align:center; margin-bottom:0px; margin-top: 0px; font-weight: bold;"><?php echo strtoupper($name); ?></p>
          <div style="font-size:11pt;text-align:center; margin-bottom:0px;">Official Employee</div>
        </div>
      </div>
      
      <div class="signature-draggable" id="doSig" style="left: <?php echo ($order['do_sig_x'] ?? 50) + 96 + 20; ?>px; top: <?php echo ($order['do_sig_y'] ?? 180) + 67.2 + 20; ?>px;">
        <div class="label">DO Signature</div>
        <?php if(!empty($order['do_signature'])): ?>
          <img src="<?php echo htmlspecialchars($order['do_signature']); ?>" style="max-width: 150px; max-height: 60px;">
        <?php else: ?>
          <div style="width: 120px; height: 60px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; font-size: 10px;">Chief Sig</div>
        <?php endif; ?>
        
      </div>
      
      <div class="signature-draggable" id="rdSig" style="left: <?php echo ($order['rd_sig_x'] ?? 480) + 96 + 20; ?>px; top: <?php echo ($order['rd_sig_y'] ?? 180) + 67.2 + 20; ?>px;">
        <div class="label">RD Signature</div>
        <?php if(!empty($order['rd_signature'])): ?>
          <img src="<?php echo htmlspecialchars($order['rd_signature']); ?>" style="max-width: 160px; max-height: 80px;">
        <?php else: ?>
          <div style="width: 120px; height: 60px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; font-size: 10px;">RD Sig</div>
        <?php endif; ?>
        
      </div>
      
      <div class="signature-draggable" id="applicantSig" style="left: <?php echo ($order['applicant_sig_x'] ?? 300) + 96 + 20; ?>px; top: <?php echo ($order['applicant_sig_y'] ?? 360) + 47.2 + 55; ?>px;">
        <div class="label">Applicant Signature</div>
        <?php if(!empty($order['applicant_signature'])): ?>
          <img src="..\<?php echo htmlspecialchars($order['applicant_signature']); ?>" style="max-width: 200px; max-height: 80px;">
        <?php else: ?>
          <div style="width: 120px; height: 60px; background: #f0f0f0; display: flex; align-items: center; justify-content: center; font-size: 10px;">Applicant Sig</div>
        <?php endif; ?>
      </div>
    </div>
    
    <form method="POST" id="positionForm">
      <input type="hidden" name="applicant_x" id="applicant_x">
      <input type="hidden" name="applicant_y" id="applicant_y">
      <input type="hidden" name="do_x" id="do_x">
      <input type="hidden" name="do_y" id="do_y">
      <input type="hidden" name="rd_x" id="rd_x">
      <input type="hidden" name="rd_y" id="rd_y">
      <div class="button-container">
        <button type="submit" class="btn btn-save">Save & Generate PDF</button>
        <button type="button" class="btn btn-cancel" onclick="window.location.href='review_to.php?id=<?php echo $order_id; ?>'">Cancel</button>
      </div>
    </form>
  </div>

<script>
    function makeDraggable(element) {
      let pos1 = 0, pos2 = 0, pos3 = 0, pos4 = 0;
      element.onmousedown = dragMouseDown;

      function dragMouseDown(e) {
        e.preventDefault();
        pos3 = e.clientX;
        pos4 = e.clientY;
        document.onmouseup = closeDragElement;
        document.onmousemove = elementDrag;
      }

      function elementDrag(e) {
        e.preventDefault();
        pos1 = pos3 - e.clientX;
        pos2 = pos4 - e.clientY;
        pos3 = e.clientX;
        pos4 = e.clientY;
        element.style.top = (element.offsetTop - pos2) + "px";
        element.style.left = (element.offsetLeft - pos1) + "px";
      }

      function closeDragElement() {
        document.onmouseup = null;
        document.onmousemove = null;
      }
    }

    makeDraggable(document.getElementById('applicantSig'));
    makeDraggable(document.getElementById('doSig'));
    makeDraggable(document.getElementById('rdSig'));

    document.getElementById('positionForm').onsubmit = function() {
      const offsetX = 96; 
      const offsetY = 67.2;
      
      const boxOffset = -20; 
      
      document.getElementById('applicant_x').value = Math.round(document.getElementById('applicantSig').offsetLeft - offsetX + boxOffset);
      document.getElementById('applicant_y').value = Math.round(document.getElementById('applicantSig').offsetTop - offsetY + (boxOffset -25));
      
      document.getElementById('do_x').value = Math.round(document.getElementById('doSig').offsetLeft - offsetX + boxOffset);
      document.getElementById('do_y').value = Math.round(document.getElementById('doSig').offsetTop - offsetY + boxOffset);
      
      document.getElementById('rd_x').value = Math.round(document.getElementById('rdSig').offsetLeft - offsetX + boxOffset);
      document.getElementById('rd_y').value = Math.round(document.getElementById('rdSig').offsetTop - offsetY + boxOffset);
    };
  </script>
</body>
</html>