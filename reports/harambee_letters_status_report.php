<?php
session_start();
header('Content-Type: application/json');
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/libraries/vendor/autoload.php');
use Dompdf\Dompdf;

// Ensure the status is valid
if (!isset($_POST['status']) || !in_array($_POST['status'], ['received', 'not_received'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid status selected.']);
    exit;
}

// Generate the timestamp
$timestamp = date('l, F j, Y g:i A'); 

$status = $_POST['status'];
$headParishId = $_SESSION['head_parish_admin_id']; 

// Encode the logo image to base64
$imageData = base64_encode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/assets/images/logos/kkkt-logo.jpg'));

// Set the correct MIME types for each
$imageSrc = 'data:image/jpeg;base64,' . $imageData;

// Build the SQL query to fetch member ids based on the status
$receivedValue = ($status == 'received') ? 'Yes' : 'No';

$reportTitle = ($receivedValue == 'Yes') ? "ORODHA YA WASHARIKA WALIOPOKEA BARUA ZA HARAMBEE" : "ORODHA YA WASHARIKA AMBAO HAWAKUPOKEA BARUA ZA HARAMBEE";
$query = "
    SELECT member_id
    FROM harambee_letter_statuses
    WHERE head_parish_id = ? AND status = ?
";
$stmt = $conn->prepare($query);
$stmt->bind_param("is", $headParishId, $receivedValue);  // Binding as string for status
$stmt->execute();
$result = $stmt->get_result();

$memberIds = [];
while ($row = $result->fetch_assoc()) {
    $memberIds[] = $row['member_id'];
}


// Fetch member details for each member ID
$members = [];
foreach ($memberIds as $id) {
    $details = getMemberDetails($conn, $id); // Assuming getMemberDetails function exists
    if ($details) {
        // Ensure that the result is fetched as an associative array
        $memberData = $details->fetch_assoc();
        $members[] = $memberData;
    }
}

// Start accumulating HTML content for the PDF
$html = '<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <style>
        body {
            font-family: helvetica;
            font-size: 11pt;
            margin: 5px 15px 15px 15px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 10px;
        }
        .header h2 {
            margin: 2px 0;
            font-size: 10pt;
            text-transform: uppercase;
            color: #2c2c2c;
        }
        .subject {
            font-weight: bold;
            margin: 20px 0 5px 0;
        }
        .content p {
            margin: 10px 0;
            line-height: 1.5;
        }
        .projects {
            margin: 10px 0;
            padding-left: 20px;
        }
        .projects ol {
            padding-left: 20px;
        }
        .projects li {
            margin-bottom: 5px;
        }
        .verse {
            font-style: italic;
            color: #444;
            margin: 10px 0;
            border-left: 3px solid #aaa;
            padding-left: 10px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
            font-size:0.7rem;
        }
        th, td {
            border: 1px solid #333;
            padding: 8px;
            text-align: left;
        }

        .footer {
            position: fixed;
            bottom: 0px;
            left: 0;
            right: 0;
            height: 30px;
            text-align: center;
            font-size: 12px;
            color: #888;
        }
        .footer .page-number:before {
            content: "Page " counter(page);
        }
    </style>
</head>
<body>
    <div class="header">
        <img src="' . $imageSrc . '" alt="KKKT Logo" style="width: 60px; height: auto; margin-bottom: 5px; display: block; margin-left: auto; margin-right: auto;">
        <h2>KKKT DAYOSISI YA KASKAZINI KATI</h2>
        <h2>JIMBO LA ARUSHA MASHARIKI | USHARIKA WA ELERAI</h2>
    </div>

    <h3 style="text-align:center;font-size:0.8rem;">' . $reportTitle . '</h3>
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Jina</th>
                <th>Simu</th>
                <th>Mtaa</th>
                <th>Jumuiya</th>
                <th>Bahasha Na.</th>
            </tr>
        </thead>
        <tbody>';

if (empty($members)) {
    $html .= '<tr>
                <td colspan="6" style="text-align:center;">No members found.</td>
              </tr>';
} else {
    foreach ($members as $index => $member) {
        // Concatenate the first name, middle name, and last name to construct the full name
        $fullName = '';
        if (isset($member['title'])) {
            $fullName .= htmlspecialchars($member['title']) . ' ';
        }
        if (isset($member['first_name'])) {
            $fullName .= htmlspecialchars($member['first_name']) . ' ';
        }
        if (isset($member['middle_name']) && $member['middle_name'] !== '') {
            $fullName .= htmlspecialchars($member['middle_name']) . ' ';
        }
        if (isset($member['last_name'])) {
            $fullName .= htmlspecialchars($member['last_name']);
        }

        // Check if the other necessary keys exist to avoid errors
        $phone = isset($member['phone']) ? (strpos($member['phone'], '255') === 0 ? '0' . substr($member['phone'], 3) : htmlspecialchars($member['phone'])) : 'N/A';
        $subParish = isset($member['sub_parish_name']) ? htmlspecialchars($member['sub_parish_name']) : 'N/A';
        $envelopeNumber = isset($member['envelope_number']) ? htmlspecialchars($member['envelope_number']) : 'N/A';
        $community = isset($member['community_name']) ? htmlspecialchars($member['community_name']) : 'N/A';

        $html .= '<tr>
                    <td>' . ($index + 1) . '</td>
                    <td>' . $fullName . '</td>
                    <td>' . $phone . '</td>
                    <td>' . $subParish . '</td>
                    <td>' . $community . '</td>
                    <td>' . $envelopeNumber . '</td>
                  </tr>';
    }
}

$html .= '</tbody>
    </table>

    <div class="footer">
        <p><span class="page-number"></span> | Printed on '.$timestamp.' | Kanisa Langu - SEWMR Technologies</p>
    </div>
</body>
</html>';


// Set the file name and path for saving the PDF
$fileName = 'letter_status_report_' . time() . '.pdf';
$savePath = $_SERVER['DOCUMENT_ROOT'] . '/logs/' . $fileName;

// Instantiate Dompdf and load HTML content
$dompdf = new Dompdf();
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Save the generated PDF to a file
file_put_contents($savePath, $dompdf->output());

// Generate the download URL (relative to the document root)
$downloadUrl = '/logs/' . $fileName;

// Return a JSON response with success message and download URL
echo json_encode([
    'success' => true,
    'message' => 'Report generated successfully.',
    'download_url' => $downloadUrl
]);
?>
