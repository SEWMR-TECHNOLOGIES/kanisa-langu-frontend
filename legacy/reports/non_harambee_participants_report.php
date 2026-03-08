<?php
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/libraries/vendor/autoload.php');

use Dompdf\Dompdf;

// Instantiate Dompdf
$dompdf = new Dompdf();
$options = $dompdf->getOptions();
$options->setFontCache('fonts');
$options->set('isRemoteEnabled', true);
$options->setChroot([$_SERVER['DOCUMENT_ROOT'] . '/assets/fonts/']);
$dompdf->setOptions($options);

$imageData = base64_encode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/assets/images/logos/kkkt-logo.jpg'));
$src = 'data:image/jpeg;base64,' . $imageData;

date_default_timezone_set('Africa/Nairobi');
$timestamp = date('l, F j, Y g:i A'); 

// Extract GET parameters
$harambee = $_GET['harambee_id'] ?? null;
$sub_parish = $_GET['sub_parish_id'] ?? null;
$community = $_GET['community_id'] ?? null;
$group = $_GET['group_id'] ?? null;
$target = $_GET['target'] ?? null;

// Validate and decrypt IDs
function validateAndDecryptId($id, $type, $isRequired = false) {
    if (empty($id)) {
        if ($isRequired) {
            header("Location: /error.php?message=$type ID is required."); exit;
        }
        return null;
    }
    try {
        $decryptedId = decryptData($id);
        if (empty($decryptedId) || !preg_match('/^[a-zA-Z0-9]+$/', $decryptedId)) {
            header("Location: /error.php?message=Invalid $type ID."); exit;
        }
        return $decryptedId;
    } catch (Exception $e) {
        header("Location: /error.php?message=" . urlencode("Failed to decrypt $type ID: " . $e->getMessage())); exit;
    }
}

$harambee_id = validateAndDecryptId($harambee, 'harambee', true);
$sub_parish_id = validateAndDecryptId($sub_parish, 'sub-parish', true);
$community_id = validateAndDecryptId($community, 'community', true);
if ($target === 'groups') $group_id = validateAndDecryptId($group, 'group', true);

if (!isset($_SESSION['head_parish_id'])) {
    header("Location: /error.php?message=" . urlencode("Unauthorized")); exit;
}
$head_parish_id = $_SESSION['head_parish_id'];
$parish_info = getParishInfo($conn, $head_parish_id);

$result = getSubParishAndCommunityNames($conn, $community_id);
$community_name = $result['community_name'] ?? "N/A";
$sub_parish_name = $result['sub_parish_name'] ?? "N/A";

$harambee_details = get_harambee_details($conn, $harambee_id, $target);

// Get non-contributing members
$non_contributors_result = getNonContributingMembers($conn, $target, $harambee_id, $head_parish_id, $community_id);
$members = $non_contributors_result['success'] ? $non_contributors_result['data'] : [];

// Sort members by first_name, then middle_name, then last_name
usort($members, function($a, $b) {
    return strcmp($a['first_name'].$a['middle_name'].$a['last_name'], $b['first_name'].$b['middle_name'].$b['last_name']);
});

$html = '
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="shortcut icon" type="image/png" href="/assets/images/logos/favicon.png" />
<title>Harambee Non-Contributors Report</title>
<style>
@font-face { font-family: "Barlow"; src: url("../assets/fonts/Barlow-Regular.ttf") format("truetype"); font-weight: normal; font-style: normal; }
*{margin:0; padding:0; box-sizing:border-box;}
body {font-family:"Barlow"; margin:25px; padding:0; color:#333; background-color:#fff;}
.container {width:90%; margin:20px auto; padding:20px; background-color:#fff; box-shadow:0 4px 8px rgba(0,0,0,0.1);}
.header {text-align:center; margin-bottom:10px;}
.header img {max-width:150px; margin-bottom:10px;}
h1 {font-size:14px; color:#333; margin-bottom:5px;}
table {width:100%; border-collapse: collapse; margin-top:10px;}
th, td {border:1px solid #000; padding:4px; font-size:12px;}
th {background-color:#fff; color:#000;}
th, td {text-align:left;}
th#col-num, td#col-num {width:10%; text-align:center;}
th#col-fname, td#col-fname {width:18%;}
th#col-mname, td#col-mname {width:18%;}
th#col-lname, td#col-lname {width:18%;}
th#col-phone, td#col-phone {width:18%;}
th#col-envelope, td#col-envelope {width:18%;}
.footer {position: fixed; bottom:0px; left:0; right:0; height:30px; text-align:center; font-size:12px; color:#888;}
.footer .page-number:before {content:"Page " counter(page);}
</style>
</head>
<body>
<div class="container">
<div class="header">
<h1 style="font-size:12px; color:#3498db;">K.K.K.T ' . $parish_info['diocese_name'] . '</h1>
<h1 style="font-size:12px; color:#2c3e50;">' . $parish_info['province_name'] . ' | ' . $parish_info['head_parish_name'] . '</h1>
</div>

<table>
<thead>
<tr>
<td colspan="6" class="text-center" style="font-weight:bold;text-align:center;">TAARIFA YA WASHARIKA AMBAO HAWAJASHIRIKI HARAMBEE YA ' . strtoupper(htmlspecialchars($harambee_details['description'])) . ' MTAA WA '. $sub_parish_name .' JUMUIYA YA '. $community_name .' | '.$timestamp.'</td>
</tr>
<tr class="text-center" style="font-weight:bold;">
<th id="col-num">#</th>
<th id="col-fname">Jina la Kwanza</th>
<th id="col-mname">Jina la Kati</th>
<th id="col-lname">Jina la Ukoo</th>
<th id="col-phone">Simu</th>
<th id="col-envelope">Bahasha Na.</th>
</tr>
</thead>
<tbody>';

$row_number = 1;
foreach ($members as $member) {
    // Replace 255 with 0 at the start if phone exists
    $phone = $member['phone'] ?? 'N/A';
    if ($phone !== 'N/A') {
        $phone = preg_replace('/^255/', '0', $phone);
    }

    $html .= '<tr>
        <td id="col-num">'.$row_number++.'</td>
        <td id="col-fname">'.htmlspecialchars($member['first_name']).'</td>
        <td id="col-mname">'.htmlspecialchars($member['middle_name']).'</td>
        <td id="col-lname">'.htmlspecialchars($member['last_name']).'</td>
        <td id="col-phone">'.htmlspecialchars($phone).'</td>
        <td id="col-envelope">'.htmlspecialchars($member['envelope_number'] ?? 'N/A').'</td>
    </tr>';
}


$html .= '
</tbody>
</table>

<div class="footer">
<p><span class="page-number"></span> | Printed on '.$timestamp.' | Kanisa Langu - SEWMR Technologies</p>
</div>
</div>
</body>
</html>';

// Load HTML into Dompdf
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

$filename = $community_name." non_contributors_report.pdf";
$dompdf->stream($filename, array("Attachment" => false));
?>
