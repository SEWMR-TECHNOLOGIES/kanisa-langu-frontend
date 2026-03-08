<?php
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/libraries/vendor/autoload.php');

use Dompdf\Dompdf;

// Check if the user is authenticated
if (!isset($_SESSION['head_parish_id'])) {
    header("Location: /error.php?message=" . urlencode("Unauthorized"));
    exit();
}

$head_parish_id = $_SESSION['head_parish_id'];

// Get date
$date = isset($_GET['report_date']) ? $_GET['report_date'] : date('Y-m-d');
$formatted_date = (new DateTime($date))->format('l, d F Y');

// Fetch parish info
$parish_info = getParishInfo($conn, $head_parish_id);

// Fetch data
$envelopeData = getDailyHeadParishRevenueStats($conn, $head_parish_id, $date);
$attendanceData = getDailyAttendanceStats($conn, $head_parish_id, $date);

// Prepare HTML
$html = '
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Envelope Usage Summary Report</title>
<style>
    /* Font Import */
    @font-face {
        font-family: "Barlow";
        src: url("../assets/fonts/Barlow-Regular.ttf") format("truetype");
        font-weight: normal;
        font-style: normal;
    }

    /* Reset & Base Styles */
    * {
        margin: 0;
        padding: 0;
        box-sizing: border-box;
    }

    body {
        font-family: Helvetica, sans-serif;
        margin: 25px;
        color: #333;
        background-color: #fff;
    }

    /* Container */
    .container {
        width: 95%;
        margin: auto;
    }

    /* Header */
    .header {
        text-align: center;
        margin-bottom: 10px;
    }
    .header h1,
    .header h2 {
        font-size: 12px;
        margin-bottom: 4px;
    }
    .date {
        color: #4187f8;
    }

    /* Tables */
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 15px;
    }
    th,
    td {
        border: 1px solid #000;
        padding: 3px;
        font-size: 10px;
    }
    th {
        background-color: #FFF;
    }

    .text-center {
        text-align: center;
    }
    .text-left {
        text-align: left;
    }
    .text-right {
        text-align: right;
    }
    /* Table Sections */
    .section-color {
        background-color: #ECEFF1;
        font-weight: bold;
    }
    .grand-total {
        background-color: #FFF;
        font-weight: bold;
    }
    .table-container {
        margin-bottom: 10px;
    }

    /* Footer */
    .footer {
        text-align: center;
        margin-top: 40px;
        font-size: 12px;
        color: #888;
    }
</style>

</head>
<body>
<div class="container">
    <div class="header">
        <h1>K.K.K.T ' . strtoupper($parish_info['diocese_name']) . ' | ' . strtoupper($parish_info['province_name']) . ' | ' . strtoupper($parish_info['head_parish_name']) . '</h1>
        <h2>ENVELOPE USAGE SUMMARY REPORT - <span class="date">' . strtoupper($formatted_date) . '</span></h2>
    </div>

    <!-- Envelope Table -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th class="section-color text-center" style="width:3%">A</th>
                    <th colspan="7" class="section-color text-center">ENVELOPE REVENUES USAGE SUMMARY</th>
                </tr>
                <tr class="text-center">
                    <th>No.</th>
                    <th class="text-left">SUB-PARISH</th>
                    <th>QTY</th>
                    <th>ENVELOPE AMOUNT</th>
                    <th>NON ENVELOPE AMOUNT</th>
                    <th>TOTAL AMOUNT</th>
                    <th>ENVELOPES %</th>
                    <th>NON ENVELOPES %</th>
                </tr>
            </thead>
            <tbody>';
            
if (!empty($envelopeData)) {
    $rowNumber = 1;
    $grandTotalQty = 0;
    $grandTotalEnvelopeAmount = 0;
    $grandTotalNonEnvelopeAmount = 0;
    $grandTotalAmount = 0;

    foreach ($envelopeData as $subName => $subData) {
        $envCount = $subData['envelope_count'] ?? 0;
        $envAmount = $subData['envelope_amount'] ?? 0;
        $totalRevenue = $subData['all_revenue_amount'] ?? 0;
        $nonEnvAmount = $subData['non_envelope_amount'] ?? ($totalRevenue - $envAmount);
        $totalAmount = $envAmount + $nonEnvAmount;

        $envelopePercentage = calculatePercentage($envAmount, $totalAmount);
        $nonEnvelopePercentage = calculatePercentage($nonEnvAmount, $totalAmount);

        $html .= '<tr>
            <td class="text-center">' . $rowNumber . '</td>
            <td class="text-left">' . htmlspecialchars($subName) . '</td>
            <td class="text-right">' . number_format($envCount, 0) . '</td>
            <td class="text-right">' . number_format($envAmount, 0) . '</td>
            <td class="text-right">' . number_format($nonEnvAmount, 0) . '</td>
            <td class="text-right">' . number_format($totalAmount, 0) . '</td>
            <td class="text-right">' . number_format($envelopePercentage, 2) . '%</td>
            <td class="text-right">' . number_format($nonEnvelopePercentage, 2) . '%</td>
        </tr>';

        $rowNumber++;
        $grandTotalQty += $envCount;
        $grandTotalEnvelopeAmount += $envAmount;
        $grandTotalNonEnvelopeAmount += $nonEnvAmount;
        $grandTotalAmount += $totalAmount;
    }

    $html .= '<tr class="grand-total">
        <td colspan="2" class="text-right">GRAND TOTAL</td>
        <td class="text-right">' . number_format($grandTotalQty,0) . '</td>
        <td class="text-right">' . number_format($grandTotalEnvelopeAmount,0) . '</td>
        <td class="text-right">' . number_format($grandTotalNonEnvelopeAmount,0) . '</td>
        <td class="text-right">' . number_format($grandTotalAmount,0) . '</td>
        <td class="text-right">' . number_format(calculatePercentage($grandTotalEnvelopeAmount, $grandTotalAmount),2) . '%</td>
        <td class="text-right">' . number_format(calculatePercentage($grandTotalNonEnvelopeAmount, $grandTotalAmount),2) . '%</td>
    </tr>';
} else {
    $html .= '<tr><td colspan="8" class="text-center">No records found for the given date.</td></tr>';
}

$html .= '</tbody>
        </table>
    </div>';


// ---------------- Attendance Table ----------------
$html .= '<div class="table-container">
        <table>
            <thead>
                <tr>
                    <th class="section-color text-center" style="width:3%">B</th>
                    <th colspan="7" class="section-color">ADULT ATTENDANCE</th>
                </tr>
                <tr class="text-center">
                    <th>No.</th>
                    <th class="text-left">MASS</th>
                    <th>MALE</th>
                    <th>FEMALE</th>
                    <th>TOTAL</th>
                    <th>BUDGET</th>
                    <th>ATTENDED MEMBERS</th>
                    <th>%</th>
                </tr>
            </thead>
            <tbody>';

$services = $attendanceData['attendance_numbers'] ?? [];
$rowNumber = 1;
$totalMale = 0;
$totalFemale = 0;
$attendanceTarget = $attendanceData['attendance_target'] ?? 0;

foreach ($services as $serviceNum => $serviceData) {
    $male = $serviceData['male'] ?? 0;
    $female = $serviceData['female'] ?? 0;
    $total = $male + $female;

    $totalMale += $male;
    $totalFemale += $female;

    $percentage = ($attendanceTarget > 0) ? ($total / $attendanceTarget) * 100 : 0;

    $html .= '<tr>
        <td class="text-center">'.$rowNumber.'</td>
        <td class="text-left">Service '.$serviceNum.'</td>
        <td class="text-right">' . number_format($male,0) . '</td>
        <td class="text-right">' . number_format($female,0) . '</td>
        <td class="text-right">' . number_format($total,0) . '</td>
        <td class="text-right">' . number_format(0,0) . '</td>
        <td class="text-right">' . number_format($total,0) . '</td>
        <td class="text-right">' . number_format($percentage,2) . '%</td>
    </tr>';

    $rowNumber++;
}

$totalAttended = $totalMale + $totalFemale;
$attendancePercentage = ($attendanceTarget > 0) ? ($totalAttended / $attendanceTarget) * 100 : 0;

$html .= '<tr class="grand-total">
    <td colspan="2" class="text-right">GRAND TOTAL</td>
    <td class="text-right">' . number_format($totalMale,0) . '</td>
    <td class="text-right">' . number_format($totalFemale,0) . '</td>
    <td class="text-right">' . number_format($totalAttended,0) . '</td>
    <td class="text-right">' . number_format($attendanceTarget,0) . '</td>
    <td class="text-right">' . number_format($totalAttended,0) . '</td>
    <td class="text-right">' . number_format($attendancePercentage,2) . '%</td>
</tr>';

date_default_timezone_set('Africa/Nairobi');
$printedOn = date('d M Y, H:i');

$html .= '</tbody>
        </table>
    </div>
</div>';

$html .= '
<div class="footer">
    <p>Kanisa Langu - SEWMR Technologies | Printed on ' . $printedOn . '</p>
</div>
</body>
</html>';
// Initialize Dompdf
$dompdf = new Dompdf();
$options = $dompdf->getOptions();
$options->setFontCache('fonts');
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);
$dompdf->setOptions($options);

// Load HTML and render PDF
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'portrait');
$dompdf->render();

// Stream PDF
$filename = "daily_envelope_attendance_report_$date.pdf";
$dompdf->stream($filename, array("Attachment" => false));

$conn->close();
?>
