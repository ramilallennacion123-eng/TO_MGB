<?php 

require_once '../vendor/autoload.php';
use Dompdf\Dompdf;
use Dompdf\Options;

$pdo = new PDO("mysql:host=localhost;dbname=to_inventory" , "root" ,"");

$id = $_GET['id'] ?? null;
if(!$id) {
  die("No ID provided.");
}
$T_order_id=(int) $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM travel_orders WHERE id = ? ");
$stmt ->execute([$id]);
$T_order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$T_order) { die("Record not found."); }

$name = $T_order['name'] ?? '';
$salary = $T_order['salary'] ?? '';
$position = $T_order['position'] ?? '';
$division_unit = $T_order['division_unit'] ??'';
$departure_date = $T_order['departure_date'] ?? '';
$official_station = $T_order['official_station'] ?? '';
$destination = $T_order['destination'] ?? '';
$arrival_date = $T_order['arrival_date'] ?? '';
$purposes_array = json_decode($T_order['purpose'], true);
$per_diems = $T_order['per_diems'] ?? '';
$assistants_array = json_decode($T_order['assistants'], true);
$appropriation = $T_order['appropriation'] ?? '';
$remarks = $T_order['remarks'] ?? '';

$new_departure_date = new DateTime($departure_date);
$new_arrival_date = new DateTime($arrival_date);
$officer_id = $T_order['officer_id'] ?? '';

function getSignatureHtml($db_path) {
    if (empty($db_path)) {
        return '';
    }
    $clean_db_path = str_replace('../', '', $db_path);
    $actual_path = dirname(__DIR__) . '/' . $clean_db_path; 

    if (file_exists($actual_path)) {
        $mime_type = mime_content_type($actual_path);
        $imgData = base64_encode(file_get_contents($actual_path));
        $src = 'data:' . $mime_type . ';base64,' . $imgData;
        
        return "<img src='" . $src . "' style='max-width: 250px; max-height: 150px; display: block; margin: 0 auto; margin-bottom: -15px;'>";
    } else {
        return "<div style='color: red; font-size: 10px;'>Error: Image not found at " . htmlspecialchars($actual_path) . "</div>";
    }
}

$applicant_sign = $T_order['applicant_signature'] ?? '';
$do_sign = $T_order['do_signature'] ?? '';
$rd_sign = $T_order['rd_signature'] ?? '';

$applicant_signatureHtml = getSignatureHtml($applicant_sign);
$do_signatureHtml        = getSignatureHtml($do_sign);
$rd_signatureHtml        = getSignatureHtml($rd_sign);

switch($officer_id){
    case '101':
        $officer_name = "Antonio C. Marasigan";
        break;
    case '102':
        $officer_name = "Arlene E Dayao";
        break;
    }

    switch ($officer_id) {
    case '101':
        $officer_position = "Engr. V/Chief MMD";
        break;
    case '102':
        $officer_position = "Division Chief";
        break;
    default:
        $officer_position = "";
    } 

$options = new Options();
$options ->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$dompdf = new Dompdf($options);


$html = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>'.strtoupper($name).'   TRAVEL ORDER </title>
    <style>
        body { font-family: "Times New Roman", Times, serif; }
        .form-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .form-table td { padding: 4px 4px; vertical-align: bottom; }
        .label-cell { white-space: nowrap; width: 1%; padding-right: 8px; vertical-align: bottom; line-height: 1; padding-bottom: 1px; }
        .line-cell { border-bottom: 1px solid black; width: 99%; text-align: left; vertical-align: bottom; line-height: 1; padding-bottom: 0px; }
        .inner-row { width: 100%; border-collapse: collapse; margin: 0; padding: 0; }
    </style>
    </head><body>';
    
    $html .= '<h6 style="text-align:center; margin-bottom:0;">Republic of the Philippines</h6>
        <h6 style="text-align:center; margin-top:0px;margin-bottom:0px;">Department of Environment and Natural Resources</h6>
        <h5 style="text-align:center; margin-top:0px; margin-bottom:0;">MINES AND GEOSCIENCES BUREAU-V</h5>
        <h6 style="text-align:center; margin-top:0;">Regional Center, Rawis, Legazpi City</h6>
        <h5 style="text-align:center; margin-bottom:0;">TRAVEL ORDER</h5>
        <p style="text-align:center; margin-top:0;">(No._________________)</p>
        <br>
        <table class="form-table">
          <tr>
              <td style="width: 50%;">
                  <table class="inner-row">
                      <tr>
                          <td class="label-cell">Name:</td>
                          <td class="line-cell"><b>'. strtoupper($name) .'</b></td>
                      </tr>
                  </table>
              </td>
              <td style="width: 50%;">
                  <table class="inner-row">
                      <tr>
                          <td class="label-cell">Salary:</td>
                          <td class="line-cell">'.$salary.'</td>
                      </tr>
                  </table>
              </td>
          </tr>
          <tr>
              <td>
                  <table class="inner-row">
                      <tr>
                          <td class="label-cell">Position:</td>
                          <td class="line-cell">'.$position.'</td>
                      </tr>
                  </table>
              </td>
              <td>
                  <table class="inner-row">
                      <tr>
                          <td class="label-cell">Div/Sec/Unit:</td>
                          <td class="line-cell">'. $division_unit.'</td>
                      </tr>
                  </table>
              </td>
          </tr>
          <tr>
              <td>
                  <table class="inner-row">
                      <tr>
                          <td class="label-cell">Departure Date:</td>
                          <td class="line-cell">'.$new_departure_date->format('F j, Y').'</td>
                      </tr>
                  </table>
              </td>
              <td>
                  <table class="inner-row">
                      <tr>
                          <td class="label-cell">Official Station:</td>
                          <td class="line-cell">'.$official_station.'</td>
                      </tr>
                  </table>
              </td>
          </tr>
          <tr>
              <td>
                  <table class="inner-row">
                      <tr>
                          <td class="label-cell">Destination:</td>
                          <td class="line-cell">'.$destination.'</td>
                      </tr>
                  </table>
              </td>
              <td>
                  <table class="inner-row">
                      <tr>
                          <td class="label-cell">Arrival Date:</td>
                          <td class="line-cell">'. $new_arrival_date->format('F j, Y').'</td>
                      </tr>
                  </table>
                </td>
            </tr>
        </table>
        <br>
        <table style="width: 100%; border-collapse: collapse; font-size:13px">
            <tr>
                <td style="width: 1%; white-space: nowrap; vertical-align: top; padding-right: 10px;">
                    Purpose of Travel:
                </td>
                <td style="vertical-align: top;">
                    <ol style="margin: 0; padding-left: 15px;">';
                    
                    if(!empty($purposes_array)){
                        foreach ($purposes_array as $purpose) {
                            $html .= '<li style="margin-bottom: 4px; text-align: justify;">' . htmlspecialchars($purpose) . '</li>';
                        }
                    }
                    
                    $html .= ' 
                    </ol>
                </td>
            </tr>
        </table>

        <br>
        <table style="width: 75%; border-collapse: collapse; margin-bottom: 2px; font-size:13px;">
            <tr>
                <td class="label-cell">Per Diems/Expenses Allowed:</td>
                <td class="line-cell">'.$per_diems.'</td>
            </tr>
        </table>
        
        <table style="width: 75%; border-collapse: collapse; margin-bottom: 2px;font-size:13px;">
            <tr>
                <td class="label-cell">Assistants or Laborers Allowed:</td>
                <td class="line-cell">';
                
                if(!empty($assistants_array)){
                    $sanitized = array_map('htmlspecialchars', $assistants_array);
                    $html .= implode(', ', $sanitized);
                }
                
                $html .= '</td>
            </tr>
        </table>
        
        <table style="width: 75%; border-collapse: collapse; margin-bottom: 2px;font-size:13px;">
            <tr>
                <td class="label-cell">Appropriations to which travel should be charged:</td>
                <td class="line-cell">'.$appropriation.'</td>
            </tr>
        </table>
        
        <table style="width: 100%; border-collapse: collapse;font-size:13px;">
            <tr>
                <td class="label-cell">Remarks or special instructions:</td>
                <td class="line-cell">'.$remarks.'</td>
            </tr>
           <tr>
                <td class="label-cell"></td>
                <td class="line-cell" style="padding-top: 18px;"></td>
            </tr>

        </table>
        
        <h5>Certification</h5>
        <div style="text-indent: 3em; font-size:13px">This is to certify that the travel is necessary and is connected with the function of the 
        official/employee of this Div/Sec/Unit.</div>

        <div style="border-bottom: 1px solid black; width: 100%; padding-bottom: 10px;">
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="width: 50%; vertical-align: bottom;">
                <p style="font-size:13px; margin-bottom: 20px;">Recommending Approval:</p>
                <div style="margin-bottom: 5px; position: relative; right:60px">'.$do_signatureHtml.'</div>
                <p style="font-size:13px; font-weight: bold; margin: 0;">
                    <u>'.strtoupper($officer_name).'</u>
                </p>
                <p style="font-size:12px; margin: 0;">'.$officer_position.'</p>
            </td>

            <td style="width: 50%; vertical-align: bottom;">
                <p style="font-size:13px; margin-bottom: 20px;">Approved:</p>
                <div style="margin-bottom: 5px; position:relative;right:50px;">'.$rd_signatureHtml.'</div>
                <p style="font-size:13px; font-weight: bold; margin: 0;">
                    <u>GUILLERMO A. MOLINA JR. IV</u>
                </p>
                <p style="font-size:12px; margin: 0;">Regional Director</p>
            </td>
        </tr>
    </table>
</div>
        
        <h5 style="text-align:center;">AUTHORIZATION</h5>
        <div style="text-indent: 2em; font-size:12px; text-align:justifty;">I hereby authorize the Accountant to deduct the corresponding amount of the unliquidated cash advance from my succeding
        for my failure to liquidate this travel within twenty(20) days upon return to my permanent official station pursuant to 
        Commission on Audit(COA) Circular No. 2012-004 dated November 28, 2012.</div>

        <div style="text-align: center; margin-top: 30px; margin-bottom: 0px;">
            ' .$applicant_signatureHtml. '
            </div>

        <h5 style="text-align:center; margin-bottom:0px; margin-top: 0px;">'.strtoupper($name).'</h5>
        <div style="font-size:12px;text-align:center; margin-bottom:0px;">Official Employee</div>
        ';

        
$html .='</body></html>';   
$dompdf->loadHtml($html);
$dompdf->setPaper('letter', 'portrait');
$dompdf->render();   
if (ob_get_contents()) {
    ob_end_clean();
} 
$dompdf->stream("Travel_Order.pdf", array("Attachment" => 0));
exit; 
?>