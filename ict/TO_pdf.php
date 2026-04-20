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

$applicant_x = ($T_order['applicant_sig_x'] ?? 300) + 7;
$applicant_y = ($T_order['applicant_sig_y'] ?? 360) + 7;
$do_x = ($T_order['do_sig_x'] ?? 50) + 7;
$do_y = ($T_order['do_sig_y'] ?? 180) + 7;
$rd_x = ($T_order['rd_sig_x'] ?? 480) + 7;
$rd_y = ($T_order['rd_sig_y'] ?? 180) + 7;

$new_departure_date = new DateTime($departure_date);
$new_arrival_date = new DateTime($arrival_date);
$officer_id = $T_order['officer_id'] ?? null;


if($officer_id){
    $officerquery = "SELECT name, position FROM users WHERE id =:id AND role = 'chief' LIMIT 1";
    $stmt = $pdo->prepare($officerquery);
    $stmt ->execute(['id' => $officer_id]);
    $officer_result = $stmt->fetch(PDO::FETCH_ASSOC);
}
if($officer_result){
    $officer_name = $officer_result['name'];
    $officer_position = $officer_result['position'];
}

$rdquerry = "SELECT name, position FROM users WHERE role = 'rd' LIMIT 1";
$stmt = $pdo->prepare($rdquerry);
$stmt ->execute();
$rd_result = $stmt->fetch(PDO::FETCH_ASSOC);

$rd_name = $rd_result['name'];
$rd_position = $rd_result['position'];

function getSignatureHtml($db_path, $maxWidth = 150, $maxHeight = 70) {
    if (empty($db_path)) {
        return '';
    }
    
    $clean_db_path = str_replace('../', '', $db_path);
    $actual_path = dirname(__DIR__) . '/' . $clean_db_path; 

    if (file_exists($actual_path)) {
        $mime_type = mime_content_type($actual_path);
        $imgData = base64_encode(file_get_contents($actual_path));
        $src = 'data:' . $mime_type . ';base64,' . $imgData;
        
        return "<img src='" . $src . "' style='max-width: " . $maxWidth . "px; max-height: " . $maxHeight . "px; display: block;'>";
    } else {
        return "<div style='color: red; font-size: 10px;'>Error: Image not found</div>";
    }
}

$applicant_sign = $T_order['applicant_signature'] ?? '';
$do_sign = $T_order['do_signature'] ?? '';
$rd_sign = $T_order['rd_signature'] ?? '';

$applicant_signatureHtml = getSignatureHtml($applicant_sign, 200, 100);
$do_signatureHtml        = getSignatureHtml($do_sign, 150, 60);
$rd_signatureHtml        = getSignatureHtml($rd_sign, 160, 80);

$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isRemoteEnabled', true);
$options->set('dpi', 96); 
$dompdf = new Dompdf($options);


$html = '<!DOCTYPE html><html><head><meta charset="utf-8"><title>'.strtoupper($name).'   TRAVEL ORDER </title>
    <style>
        body { font-family: "Times New Roman", Times, serif; position: relative; }
        .form-table { width: 100%; border-collapse: collapse; font-size: 13px; }
        .form-table td { padding: 4px 4px; vertical-align: bottom; }
        .label-cell {font-size: 11pt; white-space: nowrap; width: 1%; padding-right: 8px; vertical-align: bottom; line-height: 1; padding-bottom: 1px; }
        .line-cell { font-size: 11pt; border-bottom: 1px solid black; width: 99%; text-align: left; vertical-align: bottom; line-height: 1; padding-bottom: 0px; }
        .inner-row { width: 100%; border-collapse: collapse; margin: 0; padding: 0; }
        p{font-size:11pt;}
        @page {
            margin-top: .70in;
            margin-right: 1in;
            margin-bottom: 0.50in;
            margin-left: 1in;
        }
        /* Style for absolute signatures */
        .signature-overlay {
            position: absolute;
            z-index: 10;
        }
    </style>
    </head><body>';
        //signature
        if (!empty($applicant_sign)) {
            $html .= '<div class="signature-overlay" style="left: '.$applicant_x.'px; top: '.$applicant_y.'px;">'.$applicant_signatureHtml.'</div>';
        }
        if (!empty($do_sign)) {
            $html .= '<div class="signature-overlay" style="left: '.$do_x.'px; top: '.$do_y.'px;">'.$do_signatureHtml.'</div>';
        }
        if (!empty($rd_sign)) {
            $html .= '<div class="signature-overlay" style="left: '.$rd_x.'px; top: '.$rd_y.'px;">'.$rd_signatureHtml.'</div>';
        }
    
    $html .= '<p style=" text-align:center; margin-bottom:0;">Republic of the Philippines</p>
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
                <td style=" font-size: 11pt; width: 1%; white-space: nowrap; vertical-align: top; padding-right: 10px;">
                    Purpose of Travel:
                </td>
                <td style="vertical-align: top;">
                    <ol style="margin: 0; padding-left: 15px;">';
                    
                    if(!empty($purposes_array)){
                        foreach ($purposes_array as $purpose) {
                            $html .= '<li style="font-size: 11pt; margin-bottom: 4px; text-align: justify;">' . htmlspecialchars($purpose) . '</li>';
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
        
        <p style="font-weight: bold;">Certifications:</p>
        <div style="text-indent: 3em; font-size:11pt; text-align: justify;">This is to certify that the travel is necessary and is connected with the function of the 
        official/employee of this Div/Sec/Unit.</div>
        <table style="width: 100%; border-collapse: collapse;">
            <tr>
        <td style="width: 50%; vertical-align: bottom;">
        <p style="margin-bottom: 5px;">Recommending Approval:</p>
        </td>
        <td style="width: 50%; vertical-align: bottom;"><p style="font-size:11pt; margin-bottom: 5px;">Approved:</p></td>
        </tr>
        </table>
        

        <div style="border-bottom: 1px solid black; width: 100%; padding-bottom: 10px; margin-top: 0px; position: relative; min-height: 50px;">
    <table style="width: 100%; border-collapse: collapse;">
        <tr>
            <td style="width: 50%; vertical-align: bottom;">
                <p style="font-size:13px; font-weight: bold; margin: 40px 0 0 0; text-align: left;">
                    <u>'.strtoupper($officer_name).'</u>
                </p>
                <p style="font-size:11pt; margin: 0; text-align: left;">'.$officer_position.'</p>
            </td>

            <td style="width: 50%; vertical-align: bottom;">
                <p style="font-size:13px; font-weight: bold; margin: 40px 0 0 0; text-align: left;">
                    <u>'.strtoupper($rd_name).'</u>
                </p>
                <p style="font-size:11pt; margin: 0; text-align: left;">'.$rd_position.'</p>
            </td>
        </tr>
    </table>
</div>
        
        <p style="text-align:center; font-weight: bold; margin-top: 15px;">AUTHORIZATION</p>
        <p style="text-indent: 2em; font-size:11pt; text-align:justify;">I hereby authorize the Accountant to deduct the corresponding amount of the unliquidated cash advance from my succeding
        for my failure to liquidate this travel within twenty(20) days upon return to my permanent official station pursuant to 
        Commission on Audit(COA) Circular No. 2012-004 dated November 28, 2012.</p>

        <div style="text-align: center; margin-top: 50px; position: relative; min-height: 50px;">
            <p style="text-align:center; margin-bottom:0px; margin-top: 0px; font-weight: bold;">'.strtoupper($name).'</p>
            <div style="font-size:11pt;text-align:center; margin-bottom:0px;">Official Employee</div>
        </div>';

    
        
$html .='</body></html>';   
$dompdf->loadHtml($html);
$dompdf->setPaper('letter', 'portrait');
$dompdf->render();   
if (ob_get_contents()) {
    ob_end_clean();
} 
$dompdf->stream($name."_Travel_Order.pdf", array("Attachment" => 0));
exit; 
?>