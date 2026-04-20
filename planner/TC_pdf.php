<?php 

require_once '../vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

$pdo = new PDO("mysql:host=localhost;dbname=to_inventory", "root", "");

$id = $_GET['id'] ?? null;
if (!$id) { die("No ID provided."); }
$clearance_id = (int) $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM travel_clearances WHERE id = ?");
$stmt->execute([$id]);
$clearance = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$clearance) { die("Record not found."); }

$name = $clearance['name'] ?? '';
$location = $clearance['location'] ?? '';
$travel_date = $clearance['travel_date'] ?? '';
$purposes_array = json_decode($clearance['purpose'], true);


$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);

$path = '../images/mgb_logo.png';
$type = pathinfo($path, PATHINFO_EXTENSION);
$base64 = ''; 
if (file_exists($path)) {
    $data = file_get_contents($path);
    $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
}
$logo = "<img src='". $base64 ."' style='max-width:50px; max-height:50px;'>";

$formContent = '
          <p style="font-size: 6px; padding-left:20px;">Note: Fill out this Form in 2 copies</p>

          <table style="width: 100%; border-collapse: collapse; border: none; margin-top: -17px;">
            <tr>
              <td style="width: 30%;"></td>
              <td style="width: 40%; text-align: center; vertical-align: top; padding-top: 15px; padding-bottom:8px; font-size:14px;">
                <h6 style="margin: 0;">TRAVEL CLEARANCE</h6>
                <h6 style="margin: 0;">No. 2026-_____</h6>
              </td>
              <td style="width: 30%; text-align: right; vertical-align: top;">
                <table style="width: 110px; height: 80px; float: right; text-align: center;">
                  <tr>
                    <td style="vertical-align: middle; padding: 1px; margin-bottom: 2px;">
                      <div>'.$logo.'</div>
                      <p style="font-size: 6px; line-height: 1; margin-top: 0px;">Mines and Geosciences Bureau RO V Rawis, Legazpi City</p>
                    </td>
                  </tr>
                </table>
              </td>
            </tr>
          </table>

          <table style="width: 90%; border-collapse: collapse; border: none; margin-top: 15px; font-size:10px">
            <tr>
              <td style="width: 100%;">
               <table class="inner-row">
                 <tr>
                    <td class="label-cell">Name of Fieldmen:</td>
                    <td class="line-cell">'.$name.'</td>
                 </tr>
              </table>
              </td>
            </tr>
            <tr>
              <td style="width: 100%;">
               <table class="inner-row">
                 <tr>
                    <td class="label-cell">PAP Code:</td>
                    <td class="line-cell"></td>
                 </tr>
              </table>
              </td>
            </tr>
           <td style="width:100%;">
            <table class="inner-row">
              <tr>
                <td class="label-cell" style="vertical-align: top;">Purpose:</td>
                <td>
                  <ul style="margin: 0; padding-left: 20px;">'; 
                    if (is_array($purposes_array)) {
                        foreach ($purposes_array as $purpose) {
                            $formContent .='<li style = "font-size: 9px;"> ' . htmlspecialchars($purpose) . '</li>';
                        }
                    }
$formContent .= '</ul>
                </td>
              </tr>
            </table>
          </td>
            <tr><td style="height: 5px;"></td></tr>
              <td style = "width: 100%;">
            <table class ="inner-row">
            <tr>
              <td class="label-cell">Location:</td>
              <td>'.$location.'</td>
            </tr>
            </table>
            </td>
            <tr><td style="height:0px; margin-top:-5px;"></td></tr>
            <td style = "width: 100%;">
            <table class ="inner-row">
            <tr>
              <td class="label-cell">Travel Date:</td>
              <td>'.$travel_date.'</td>
            </tr>
            </table>
            </td>
            <tr><td style="height: 20px;"></td></tr>
            <tr>
              <td class="label-cell">Basis of Approval:</td>
            </tr>
            <tr><td style="height: 2px;"></td></tr>
            <tr>
              <td class="check-cell">____Fieldwork within the Approved Travel Plan:</td>
            </tr>
            <tr>
               <td class="inner-check-cell">____Within the scheduled period</td>
            </tr>
            <tr>
              <td class="inner-check-cell">____Outside the scheduled period</td>
            </tr>
            <tr>
              <td class="inner-check-cell">____(Previous travel report endorsed to the <br>
                      ORD, indicate the Date of the endorsement and corrensponding TC No.)</td>
            </tr>
            <tr>
              <td class="inner-check-cell">____(Copy of Instruction/s from the Regional Director/Division Chief)</td>
            </tr>
            <tr>
               <td class="inner-check-cell">____(Copy of Invitaton, Memorandum or Special Order)</td>
            </tr>
            <tr>
               <td class="check-cell">____(Fieldwork not within the Approved Travel Plan)</td>
            </tr>
            <tr>
               <td class="check-cell">____Intervening Activity</td>
            </tr>
            <tr>
              <td style="width: 100%;">
              <table class="inner-row">
                <tr>
                  <td class="label-cell">Remarks:</td>
                  <td class="line-cell"></td>
                </tr>
                <tr>
                  <td class="label-cell"></td>
                  <td class="line-cell" style="padding-top: 15px;"></td>
                </tr>
              </table>
              </td>
            </tr>
            <tr><td style="height: 10px;"></td></tr>
            <tr>
              <td class="label-cell">Reviewed by:</td>
            </tr>
            <tr>
              <td style="text-align: center; vertical-align: top; padding-top: 15px; padding-bottom:8px; font-size:14px;">
                <h6 style="margin: 0;"><u>JOSIE JACOB</u></h6>
                <p style="font-size:9px; margin:0;">Planning Officer II</p>
              </td> 
            </tr>
          </table>';

$html = '<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>TRAVEL CLEARANCE</title>
  <style>
    body {
      font-family: Arial, Helvetica, sans-serif;
    }
    .label-cell { white-space: nowrap; width: 1%; padding-right: 8px; vertical-align: bottom; line-height: 1; padding-bottom: 1px; font-weight:bold; padding-left:20px; font-size:9px;}
    .line-cell { border-bottom: 1px solid black; width: 100%; text-align: left; vertical-align: bottom; line-height: 1; padding-bottom: 0px;  }
    .inner-row { width: 100%; border-collapse: collapse; margin: 0; padding: 0; }
    .check-cell{  white-space: nowrap; width: 1%; padding-right: 8px; vertical-align: bottom; line-height: 2; padding-bottom: 1px; padding-left:40px; font-size:7px;}
    .inner-check-cell{  white-space: nowrap; width: 1%; padding-right: 8px; vertical-align: bottom; line-height: 2  ; padding-bottom: 1px; padding-left:55px; font-size:7px;}
    .form-container { border: 2px solid black; height: 480px; position: relative; }
  </style>
</head>
<body>

  <table style="width: 100%; border-collapse: collapse; border: none; margin: 0; padding: 0;">
    <tr>
      <td style="width: 49%; vertical-align: top; padding-right: 1%;">
        <div class="form-container">'.$formContent.'</div>
      </td>
      
      <td style="width: 49%; vertical-align: top; padding-left: 1%;">
        <div class="form-container">'.$formContent.'</div>
      </td>
    </tr>
  </table>
</body>
</html>';

$dompdf->loadHtml($html);
$dompdf->setPaper('letter', 'portrait');
$dompdf->render();   
if (ob_get_contents()) {
    ob_end_clean();
} 
$dompdf->stream("travel_clearance.pdf", array("Attachment" => 0));
exit; 
?>