<?php
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;

date_default_timezone_set('Africa/Nairobi');
$timestamp = date('l, F j, Y g:i A');

// Initialize and validate incoming parameters (same logic as your PDF)
$harambee_id = isset($_GET['harambee_id']) ? $_GET['harambee_id'] : null;
$target = isset($_GET['target']) ? $_GET['target'] : null;
$category = isset($_GET['category']) ? $_GET['category'] : 'completed';
$sub_parish_id = isset($_GET['sub_parish_id']) ? $_GET['sub_parish_id'] : null;
$exclude_members_in_groups = isset($_GET['exclude_members_in_groups']) ? $_GET['exclude_members_in_groups'] : false;
$report_for = isset($_GET['report_for']) ? $_GET['report_for'] : null;
$community_id = isset($_GET['community_id']) ? $_GET['community_id'] : null;

// Decrypt community_id if report is for community
if ($report_for === 'community' && $community_id) {
    try {
        $community_id = decryptData($community_id);
        if (empty($community_id) || !preg_match('/^[a-zA-Z0-9]+$/', $community_id)) {
            echo json_encode(["success" => false, "message" => "Invalid community ID."]);
            exit;
        }
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Error decrypting community ID: " . $e->getMessage()]);
        exit;
    }
}

// Validate harambee_id
if ($harambee_id) {
    try {
        $harambee_id = decryptData($harambee_id);
        if (empty($harambee_id) || !preg_match('/^[a-zA-Z0-9]+$/', $harambee_id)) {
            echo json_encode(["success" => false, "message" => "Invalid harambee ID."]);
            exit;
        }
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Error decrypting harambee ID: " . $e->getMessage()]);
        exit;
    }
} else {
    echo json_encode(["success" => false, "message" => "Harambee ID is required."]);
    exit;
}

// Validate sub_parish_id if required
if ($sub_parish_id) {
    try {
        $sub_parish_id = decryptData($sub_parish_id);
        if (empty($sub_parish_id) || !preg_match('/^[a-zA-Z0-9]+$/', $sub_parish_id)) {
            echo json_encode(["success" => false, "message" => "Invalid sub parish ID."]);
            exit;
        }
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Error decrypting sub parish ID: " . $e->getMessage()]);
        exit;
    }
} else {
    echo json_encode(["success" => false, "message" => "Sub Parish ID is required."]);
    exit;
}

// Map category to Swahili label (same as PDF)
$sw_category_name = '';
switch($category){
    case 'completed':
        $sw_category_name = 'WALIO MALIZA';
        break;
    case 'on_progress':
        $sw_category_name = 'WANAOENDELEA';
        break;
    case 'not_contributed':
        $sw_category_name = 'AMBAO HAWAJATOA';
        break;
    default:
        $sw_category_name = "INVALID CATEGORY";
        break;
}

// Validate target and category (same as PDF)
$valid_targets = ['head-parish', 'sub-parish', 'community', 'groups'];
if (!in_array($target, $valid_targets)) {
    echo json_encode(["success" => false, "message" => "Invalid target type provided."]);
    exit;
}

if (empty($category) || !in_array($category, ['completed', 'on_progress', 'not_contributed'])) {
    echo json_encode(["success" => false, "message" => "Invalid category selected."]);
    exit;
}

if (empty($sub_parish_id) || !preg_match('/^[a-zA-Z0-9]+$/', $harambee_id)) {
    echo json_encode(["success" => false, "message" => "Invalid sub-parish ID."]);
    exit;
}

if (!isset($_SESSION['head_parish_id'])) {
    header("Location: /error.php?message=" . urlencode("Unauthorized"));
    exit();
}

$head_parish_id = $_SESSION['head_parish_id'];
$parish_info = getParishInfo($conn, $head_parish_id);
$harambee_details = get_harambee_details($conn, $harambee_id, $target);

// Determine the target table name (not strictly needed here but keep parity)
$target_table = '';
switch ($target) {
    case 'head-parish':
        $target_table = 'head_parish_harambee_contribution';
        break;
    case 'sub-parish':
        $target_table = 'sub_parish_harambee_contribution';
        break;
    case 'community':
        $target_table = 'community_harambee_contribution';
        break;
    case 'groups':
        $target_table = 'groups_harambee_contribution';
        break;
    default:
        echo json_encode(["success" => false, "message" => "Invalid target type provided"]);
        exit();
}

// Build members array - keep exact logic as PDF
$all_member_ids = getHarambeeMemberIds($conn, $harambee_id, $target);
$all_member_details_array = [];
$all_members_processed_groups = [];

foreach ($all_member_ids as $member_id) {
    $member = getSingleMemberHarambeeDetails($conn, $member_id, $harambee_id, $target);

    // Filter by sub_parish_id
    if ($sub_parish_id && $member['sub_parish_id'] != $sub_parish_id) {
        continue;
    }

    // Filter by community if required
    if ($report_for === 'community' && $community_id && $member['community_id'] != $community_id) {
        continue;
    }

    if ($exclude_members_in_groups) {
        if ($member['is_in_groups']) {
            continue;
        }
    }

    if ($member['group_name'] != null) {
        if (in_array($member['group_name'], $all_members_processed_groups)) {
            continue;
        }
        $full_name = $member['group_name'];
        $all_members_processed_groups[] = $member['group_name'];
    } else {
        $full_name = getMemberFullName($member);
    }

    $memberDetails = getMemberTargetAndContributions($conn, $harambee_id, $member_id, $target);
    if ($memberDetails === false) {
        echo json_encode(["success" => false, "message" => "Invalid target or unable to fetch details"]);
        exit;
    }

    $target_amount = $memberDetails['target_amount'];
    $total_contribution = $memberDetails['total_contribution'];
    $balance = ($target_amount > 0) ? ($target_amount - $total_contribution) : 0;
    $percentage = $target_amount > 0 ? calculatePercentage($total_contribution, $target_amount) : 0.00;

    if ($target_amount > 0 && $total_contribution == 0) {
        $contribution_category = 'not_contributed';
    } elseif ($target_amount >= 0 && $total_contribution >= $target_amount && $total_contribution > 0) {
        $contribution_category = 'completed';
    } elseif ($target_amount > 0 && $total_contribution < $target_amount) {
        $contribution_category = 'on_progress';
    }

    $all_member_details_array[] = [
        'name' => htmlspecialchars($full_name),
        'member_id' => $member['member_id'],
        'sub_parish_id' => $member['sub_parish_id'],
        'community_id' => $member['community_id'],
        'first_name' => $member['first_name'],
        'middle_name' => $member['middle_name'],
        'last_name' => $member['last_name'],
        'envelope_number' => $member['envelope_number'],
        'title' => $member['title'],
        'member_type' => $member['member_type'],
        'phone' => $member['phone'],
        'email' => $member['email'],
        'diocese_name' => $member['diocese_name'],
        'province_name' => $member['province_name'],
        'head_parish_name' => $member['head_parish_name'],
        'sub_parish_name' => str_replace('MTAA WA ', '', $member['sub_parish_name']),
        'community_name' => $member['community_name'],
        'harambee_description' => $member['harambee_description'],
        'is_in_groups' => $member['is_in_groups'],
        'target' => $target_amount,
        'contribution' => $total_contribution,
        'balance' => $balance,
        'percentage' => $percentage,
        'category' => $contribution_category
    ];
}

// Sort same as PDF
usort($all_member_details_array, function ($a, $b) {
    return strcmp($a['first_name'], $b['first_name']);
});

// Totals same as PDF
$total_harambee_contribution = getTotalContributionBySubParishFromArray($all_member_details_array, $sub_parish_id);

// Prepare Spreadsheet (replicating the PDF layout and values)
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setTitle('Harambee Statement');

// Top headers
$sub_parish_name = getSubParishName($sub_parish_id, $conn);
$community_name_for_title = ($report_for === 'community' && $community_id) ? ' - ' . getCommunityName($community_id, $conn) : '';
$harambee_desc_upper = strtoupper(htmlspecialchars($harambee_details['description']));
$target_amount_formatted = number_format($harambee_details['amount'], 0);
$period_text = htmlspecialchars(date("d M Y", strtotime($harambee_details['from_date']))) . ' HADI ' . htmlspecialchars(date("d M Y", strtotime($harambee_details['to_date'])));

// Header rows (A1..G4)
$sheet->setCellValue('A1', "K.K.K.T " . $parish_info['diocese_name'] . " - " . $parish_info['province_name'] . " | " . $parish_info['head_parish_name']);
$sheet->setCellValue('A2', "TAARIFA YA MAPATO YA HARAMBEE YA $harambee_desc_upper MTAA WA " . strtoupper($sub_parish_name) . ($report_for === 'community' && $community_id ? " JUMUIYA YA " . strtoupper(getCommunityName($community_id, $conn)) : "") . " KWA " . $sw_category_name);
$sheet->setCellValue('A3', "LENGO: TZS $target_amount_formatted");
$sheet->setCellValue('A4', "KIPINDI: $period_text");
$sheet->setCellValue('A5', "Printed on: $timestamp");

// Merge header cells across A..G
$sheet->mergeCells('A1:G1');
$sheet->mergeCells('A2:G2');
$sheet->mergeCells('A3:G3');
$sheet->mergeCells('A4:G4');
$sheet->mergeCells('A5:G5');

// Style headers
$sheet->getStyle('A1:A5')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle('A1')->getFont()->setBold(true);
$sheet->getStyle('A2')->getFont()->setBold(true);

// Table column headers start at row 7 (to leave space)
$startRow = 7;
$headers = ['#', 'Jina', 'Simu', 'Ahadi', 'Taslimu', 'Salio', 'Mafanikio %'];
$sheet->fromArray($headers, null, 'A' . $startRow);

// Header style
$sheet->getStyle("A{$startRow}:G{$startRow}")->getFont()->setBold(true);
$sheet->getStyle("A{$startRow}:G{$startRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle("A{$startRow}:G{$startRow}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('D9E1F2');
$sheet->getStyle("A{$startRow}:G{$startRow}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

// Data rows
$row = $startRow + 1;
$counter = 1;
$totalTarget = 0;
$totalContributed = 0;
$totalBalance = 0;

foreach ($all_member_details_array as $member) {
    if ($member['category'] !== $category) {
        continue;
    }

    $totalTarget += $member['target'];
    $totalContributed += $member['contribution'];
    $totalBalance += $member['balance'];

    // Format phone like PDF: if starts with 255 replace with 0 prefix
    $phone = strtoupper($member['phone']);
    if (substr($phone, 0, 3) === '255') {
        $phone = '0' . substr($phone, 3);
    }

    // Prepare balance display as in PDF: if negative, prefix plus sign and show abs, color green
    $balance_display = ($member['balance'] < 0 ? "+" : "") . number_format(abs($member['balance']), 0);
    $target_display = $member['target'];
    $contribution_display = $member['contribution'];
    $percentage_display = number_format($member['percentage'], 2);

    // Populate row cells A..G
    $sheet->setCellValue('A' . $row, $counter);
    $sheet->setCellValue('B' . $row, strtoupper($member['name']));
    $sheet->setCellValue('C' . $row, $phone);
    $sheet->setCellValue('D' . $row, $target_display);
    $sheet->setCellValue('E' . $row, $contribution_display);
    $sheet->setCellValue('F' . $row, $balance_display);
    $sheet->setCellValue('G' . $row, $percentage_display . '%');

    // Number formats for numeric columns (D, E) and keep F as text because of +/- sign
    $sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
    $sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

    // Alignments
    $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
    $sheet->getStyle("B{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    $sheet->getStyle("C{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_LEFT);
    $sheet->getStyle("D{$row}:E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    $sheet->getStyle("F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
    $sheet->getStyle("G{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

    // Styling: if member is in groups, color the row blue (font) similar to PDF 'color:blue;'
    if ($member['is_in_groups']) {
        $sheet->getStyle("A{$row}:G{$row}")->getFont()->getColor()->setRGB('0000FF'); // blue
    }

    // If balance < 0 (PDF used green color), set font color green for F column
    if ($member['balance'] < 0) {
        $sheet->getStyle("F{$row}")->getFont()->getColor()->setRGB('008000'); // green
    }

    // Add thin borders for the row
    $sheet->getStyle("A{$row}:G{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

    $row++;
    $counter++;
}

// Grand total row (one row after data)
$sheet->setCellValue('A' . $row, ''); // empty
$sheet->setCellValue('B' . $row, 'Jumla Kuu');
$sheet->setCellValue('D' . $row, $totalTarget);
$sheet->setCellValue('E' . $row, $totalContributed);
$sheet->setCellValue('F' . $row, ($totalBalance < 0 ? "+" : "") . number_format(abs($totalBalance), 0));
$sheet->setCellValue('G' . $row, '-');

// Style grand total
$sheet->mergeCells("A{$row}:C{$row}");
$sheet->getStyle("A{$row}:G{$row}")->getFont()->setBold(true);
$sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle("D{$row}:E{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);
$sheet->getStyle("F{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_RIGHT);

// Apply number format for D, E and F (F is text in PDF due to plus sign; keep same display as PDF)
$sheet->getStyle("D{$row}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);
$sheet->getStyle("E{$row}")->getNumberFormat()->setFormatCode(NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1);

// Add borders to total row
$sheet->getStyle("A{$row}:G{$row}")->getBorders()->getAllBorders()->setBorderStyle(Border::BORDER_THIN);

// Auto size columns A..G (keep reasonable performance)
foreach (range('A', 'G') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Footer note row below totals
$footerRow = $row + 2;
$sheet->setCellValue('A' . $footerRow, "Kanisa Langu - SEWMR Technologies");
$sheet->mergeCells("A{$footerRow}:G{$footerRow}");
$sheet->getStyle("A{$footerRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
$sheet->getStyle("A{$footerRow}")->getFont()->setItalic(true);

// Construct filename consistent with PDF naming but xlsx
$filename = $sub_parish_name . $community_name_for_title . " Harambee Contribution Report " . $sw_category_name . ".xlsx";

// Send headers for download
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"" . preg_replace('/[\\\\\/:*?"<>|]/', '', $filename) . "\"");
header('Cache-Control: max-age=0');

// Write and output
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
