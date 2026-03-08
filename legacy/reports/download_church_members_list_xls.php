<?php
session_start();
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

date_default_timezone_set('Africa/Nairobi');
$timestamp = date('l, F j, Y g:i A');

// Validate
$sub_parish_id = $_GET['sub_parish_id'] ?? null;
$community_id = $_GET['community_id'] ?? null;

if (!isset($_SESSION['head_parish_id'])) {
    die("Unauthorized");
}
$head_parish_id = $_SESSION['head_parish_id'];
$parish_info = getParishInfo($conn, $head_parish_id);
$result = getSubParishAndCommunityNames($conn, $community_id);

$community_name = $result['community_name'] ?? "N/A";
$sub_parish_name = $result['sub_parish_name'] ?? "N/A";
$gender = trim($_GET['gender']);

switch ($gender) {
    case 'Male':
        $gender_text = 'JINSIA YA KIUME';
        break;
    case 'Female':
        $gender_text = 'JINSIA YA KIKE';
        break;
    case '':
        $gender_text = 'HAWANA TAARIFA ZA JINSIA';
        break;
    case 'all':
    default:
        $gender_text = 'JINSIA ZOTE';
        break;
}


// Fetch members
$members = [];
$memberIds = getMemberIdsByLocation($conn, $head_parish_id, $sub_parish_id, $community_id, $gender);
foreach ($memberIds as $memberId) {
    $details = getMemberDetails($conn, $memberId)->fetch_assoc();
    if ($details) {
        $details['full_name'] = trim($details['title'] . ' ' . $details['first_name'] . ' ' . $details['middle_name'] . ' ' . $details['last_name']);
        $members[] = $details;
    }
}

// Sort by name
usort($members, fn($a, $b) => strcmp($a['full_name'], $b['full_name']));

// Create spreadsheet
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle("Community Members");

// Existing headers
$headerText = "K.K.K.T " . $parish_info['diocese_name'] . " - " . $parish_info['province_name'] . " | " . $parish_info['head_parish_name'];
$subHeaderText = "ORODHA YA WANAJUMUIYA MTAA WA " . strtoupper($sub_parish_name) . " - JUMUIYA YA " . strtoupper($community_name);

$sheet->setCellValue('A1', $headerText);
$sheet->setCellValue('A2', $subHeaderText);
$sheet->setCellValue('A3', 'Printed on: ' . $timestamp);

// New gender row (row 4)
$sheet->setCellValue('A4', $gender_text);

// Merge cells and center alignment for all header rows including gender
$sheet->mergeCells('A1:F1');
$sheet->mergeCells('A2:F2');
$sheet->mergeCells('A3:F3');
$sheet->mergeCells('A4:F4');

$sheet->getStyle('A1:A4')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER);

// Table Headers
$sheet->fromArray(['#', 'JINA', 'SIMU', 'BAHASHA', 'MTAA', 'JUMUIYA'], null, 'A5');
$sheet->getStyle('A5:F5')->getFont()->setBold(true);
$sheet->getStyle('A5:F5')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9E1F2');
$sheet->getStyle('A5:F5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

// Data Rows
$row = 6;
$counter = 1;
foreach ($members as $details) {
    $phone = !empty($details['phone']) ? preg_replace('/^255/', '0', $details['phone']) : '';
    $envelope = $details['envelope_number'] ?? '';
    $rowData = [
        $counter++,
        $details['full_name'],
        $phone,
        $envelope,
        $details['sub_parish_name'],
        $details['community_name']
    ];

    $sheet->fromArray($rowData, null, 'A' . $row);

    // Style for Mgeni
    if ($details['type'] === 'Mgeni') {
        $sheet->getStyle("A$row:F$row")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D0E7FF');
    }

    $row++;
}

// Auto-size columns
foreach (range('A', 'F') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Set headers for download
$filename = $community_name . '_members_list.xlsx';
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$filename\"");
header('Cache-Control: max-age=0');

// Save to output
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
