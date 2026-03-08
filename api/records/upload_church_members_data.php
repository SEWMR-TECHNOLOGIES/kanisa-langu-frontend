<?php
session_start();
header('Content-Type: application/json');
require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;

if (!isset($_SESSION['head_parish_id'])) {
    echo json_encode(["success" => false, "message" => "Head Parish ID is missing from session"]);
    exit();
}

$head_parish_id = $_SESSION['head_parish_id'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $sub_parish_id = isset($_POST['sub_parish_id']) ? intval($_POST['sub_parish_id']) : null;
    $community_id = isset($_POST['community_id']) ? intval($_POST['community_id']) : null;

    if (!$sub_parish_id || !$community_id) {
        echo json_encode(["success" => false, "message" => "Sub parish ID or community ID missing"]);
        exit();
    }

    if (!isset($_FILES['member_data']) || $_FILES['member_data']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(["success" => false, "message" => "File upload failed or no file provided"]);
        exit();
    }

    $filePath = $_FILES['member_data']['tmp_name'];
    $reader = new Xlsx();
    $spreadsheet = $reader->load($filePath);
    $sheet = $spreadsheet->getActiveSheet();
    $data = $sheet->toArray(null, true, true, true);

    $output = new Spreadsheet();
    $outputSheet = $output->getActiveSheet();
    $outputSheet->setCellValue('A1', 'First Name')
                ->setCellValue('B1', 'Middle Name')
                ->setCellValue('C1', 'Last Name')
                ->setCellValue('D1', 'Envelope Number')
                ->setCellValue('E1', 'Phone')
                ->setCellValue('F1', 'Sub Parish')
                ->setCellValue('G1', 'Community')
                ->setCellValue('H1', 'Status');

    $stmtSubParish = $conn->prepare("SELECT sub_parish_name FROM sub_parishes WHERE sub_parish_id = ? AND head_parish_id = ?");
    $stmtSubParish->bind_param("ii", $sub_parish_id, $head_parish_id);
    $stmtSubParish->execute();
    $resultSubParish = $stmtSubParish->get_result();
    $subParishName = $resultSubParish->fetch_assoc()['sub_parish_name'] ?? '';

    $stmtCommunity = $conn->prepare("SELECT community_name FROM communities WHERE community_id = ? AND head_parish_id = ? AND sub_parish_id = ?");
    $stmtCommunity->bind_param("iii", $community_id, $head_parish_id, $sub_parish_id);
    $stmtCommunity->execute();
    $resultCommunity = $stmtCommunity->get_result();
    $communityName = $resultCommunity->fetch_assoc()['community_name'] ?? '';

    $updateStmt = $conn->prepare("
        UPDATE church_members 
        SET first_name = ?, middle_name = ?, last_name = ?, phone = ?
        WHERE envelope_number = ? AND head_parish_id = ?
    ");
    $checkStmt = $conn->prepare("
        SELECT member_id, community_id FROM church_members 
        WHERE envelope_number = ? AND head_parish_id = ?
    ");
    $updateCommunityStmt = $conn->prepare("
        UPDATE church_members SET community_id = ? WHERE member_id = ?
    ");
    $insertStmt = $conn->prepare("
        INSERT INTO church_members (first_name, middle_name, last_name, phone, envelope_number, head_parish_id, sub_parish_id, community_id)
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ");

    $rowNum = 2;
    foreach ($data as $index => $row) {
        if ($index === 1) continue;

        $sn = $row['B'];
        $fullName = trim($row['B'] ?? '');
        $envelopeNumber = trim($row['C'] ?? '');
        $phone = trim($row['D'] ?? '');

        if (!$sn) continue;

        $skipReason = '';

        if (!$fullName) {
            $skipReason = 'Missing full name';
        } elseif (!$envelopeNumber) {
            $skipReason = 'Missing envelope number';
        }

        $cleanName = preg_replace('/\./', '', $fullName);
        $nameParts = preg_split('/\s+/', trim($cleanName));

        $firstName = $nameParts[0] ?? '';
        $lastName = count($nameParts) > 1 ? array_pop($nameParts) : '';
        $middleName = count($nameParts) > 1 ? implode(' ', array_slice($nameParts, 1)) : '';

        if ($phone) {
            if (str_starts_with($phone, '0')) {
                $phone = '255' . substr($phone, 1);
            } elseif (!str_starts_with($phone, '255')) {
                $phone = '255' . $phone;
            }
        }

        if ($skipReason === 'Missing envelope number') {
            // Insert anyway with NULL as envelope number
            $nullEnvelope = null;
            $insertStmt->bind_param("sssssiis", $firstName, $middleName, $lastName, $phone, $nullEnvelope, $head_parish_id, $sub_parish_id, $community_id);
            $insertStmt->execute();
        }

        else if ($skipReason) {
            $outputSheet->setCellValue('A' . $rowNum, $firstName)
                        ->setCellValue('B' . $rowNum, $middleName)
                        ->setCellValue('C' . $rowNum, $lastName)
                        ->setCellValue('D' . $rowNum, $envelopeNumber)
                        ->setCellValue('E' . $rowNum, $phone)
                        ->setCellValue('F' . $rowNum, $subParishName)
                        ->setCellValue('G' . $rowNum, $communityName)
                        ->setCellValue('H' . $rowNum, $skipReason);
            $rowNum++;
            continue;
        }

        $checkStmt->bind_param("si", $envelopeNumber, $head_parish_id);
        $checkStmt->execute();
        $checkResult = $checkStmt->get_result();

        if ($checkResult->num_rows > 0) {
            $member = $checkResult->fetch_assoc();
            $member_id = $member['member_id'];
            $existing_community_id = $member['community_id'];

            $updateStmt->bind_param("sssssi", $firstName, $middleName, $lastName, $phone, $envelopeNumber, $head_parish_id);
            $updateStmt->execute();

            if (is_null($existing_community_id)) {
                $updateCommunityStmt->bind_param("ii", $community_id, $member_id);
                $updateCommunityStmt->execute();
            }
        } else {
            $insertStmt->bind_param("sssssiis", $firstName, $middleName, $lastName, $phone, $envelopeNumber, $head_parish_id, $sub_parish_id, $community_id);
            $insertStmt->execute();
        }
    }

    $filename = 'uploads/non_existing_members_' . date('Ymd_His') . '.xlsx';
    $writer = new XlsxWriter($output);
    $writer->save($_SERVER['DOCUMENT_ROOT'] . '/' . $filename);

    echo json_encode([
        "success" => true,
        "message" => "Existing members updated. New members inserted.",
        "download_url" => '/' . $filename
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}
?>
