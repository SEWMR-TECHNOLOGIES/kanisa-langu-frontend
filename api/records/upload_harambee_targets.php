<?php
session_start();
header('Content-Type: application/json');
require_once($_SERVER['DOCUMENT_ROOT'] . '/vendor/autoload.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Reader\Xlsx;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx as XlsxWriter;

// Check if head_parish_id is in session
if (!isset($_SESSION['head_parish_id'])) {
    echo json_encode(["success" => false, "message" => "Head Parish ID is missing from session"]);
    exit();
}

$head_parish_id = $_SESSION['head_parish_id'];

// Check if the request method is POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sanitize and validate inputs
    $target = isset($_POST['target']) ? trim($_POST['target']) : null; // e.g., head-parish, sub-parish, etc.
    $harambee_id = isset($_POST['harambee_id']) ? trim($_POST['harambee_id']) : null;
    $target_type = isset($_POST['target_type']) ? trim($_POST['target_type']) : null; // individual or group
    $local_timestamp = isset($_POST['local_timestamp']) ? $conn->real_escape_string(trim($_POST['local_timestamp'])) : null;

    // Validate target
    $valid_targets = ['head-parish', 'sub-parish', 'community', 'groups'];
    if (empty($target) || !in_array($target, $valid_targets)) {
        echo json_encode(["success" => false, "message" => "Invalid or missing target"]);
        exit();
    }
    
    // Validate harambee_id
    if (empty($harambee_id) || !is_numeric($harambee_id)) {
        echo json_encode(["success" => false, "message" => "Valid Harambee ID is required"]);
        exit();
    }
    
    // Validate target_type
    $valid_target_types = ['individual', 'group'];
    if (empty($target_type) || !in_array($target_type, $valid_target_types)) {
        echo json_encode(["success" => false, "message" => "Invalid or missing target type"]);
        exit();
    }
    
    // Validate local timestamp (optional format check)
    if (empty($local_timestamp)) {
        echo json_encode(["success" => false, "message" => "Local timestamp is required"]);
        exit();
    }
    
    // Validate file upload
    if (!isset($_FILES['harambee_data']) || $_FILES['harambee_data']['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(["success" => false, "message" => "File upload failed or no file provided"]);
        exit();
    }


    $filePath = $_FILES['harambee_data']['tmp_name'];
    $fileName = $_FILES['harambee_data']['name'];
    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

    // Validate file extension
    if (strtolower($fileExtension) !== 'xlsx') {
        echo json_encode(["success" => false, "message" => "Invalid file type. Only .xlsx files are allowed"]);
        exit();
    }

    try {
        // Use the appropriate reader for XLSX files
        $reader = new Xlsx();
        $spreadsheet = $reader->load($filePath);
        $sheets = $spreadsheet->getAllSheets();

        $results = [];
        $missingMemberDetails = []; // List for entries with missing member details

        foreach ($sheets as $sheetIndex => $sheet) {
            $sheetData = $sheet->toArray(null, true, true, true);
            foreach ($sheetData as $rowIndex => $row) {
                if ($rowIndex === 1) continue; // Skip header row
                
                // Skip completely empty rows
                if (empty(array_filter($row, fn($cell) => !is_null($cell) && trim($cell) !== ''))) {
                    continue;
                }
                if ($target_type === 'individual') {
                    $envelopeNumber = isset($row['C']) ? trim($row['C']) : null;
                    $targetAmount = isset($row['D']) ? floatval(str_replace(',', '', $row['D'])) : null;
            
                    if ($envelopeNumber && $targetAmount > 0) {
                        $memberDetails = getMemberDetailsByEnvelope($conn, $envelopeNumber);
                        if ($memberDetails) {
                            $member_id = $memberDetails['member_id'];
                            $sub_parish_id = $memberDetails['sub_parish_id'];
                            $community_id = $memberDetails['community_id'];
                            // NEW: check for null community_id
                            if (is_null($community_id)) {
                                // $missingMemberDetails[] = [
                                //     'sheet'          => $sheet->getTitle(),
                                //     'row'            => $rowIndex,
                                //     'envelope_number'=> $envelopeNumber,
                                //     'member_id'      => $member_id,
                                //     'sub_parish_id'  => $sub_parish_id,
                                //     'community_id'   => $community_id,
                                //     'target_amount'  => $targetAmount
                                // ];
                                // error_log($member_id);
                                continue;
                            }
                            
                            $success = recordHarambeeTarget(
                                $conn,
                                $harambee_id,
                                'individual',
                                $target,
                                $targetAmount,
                                $head_parish_id,
                                $sub_parish_id,
                                $community_id,
                                $member_id
                            );
            
                            if ($success) {
                                sendHarambeeTargetSMS($conn, $member_id, $targetAmount, $target, $harambee_id, 'individual');
                                $results[] = [
                                    'row' => $rowIndex,
                                    'envelope_number' => $envelopeNumber,
                                    'target_amount' => $targetAmount
                                ];
                            }
                        } else {
                            $missingMemberDetails[] = [
                                'sheet' => $sheet->getTitle(),
                                'row' => $rowIndex,
                                'envelope_number' => $envelopeNumber,
                                'target_amount' => $targetAmount
                            ];
                        }
                    }
                } elseif ($target_type === 'group') {
                    $groupName = isset($row['B']) ? trim($row['B']) : null;
                    $envelope1 = isset($row['C']) ? trim($row['C']) : null;
                    $envelope2 = isset($row['D']) ? trim($row['D']) : null;
                    $groupAmount = isset($row['E']) ? floatval(str_replace(',', '', $row['E'])) : null;

            
                    if ($envelope1 && $envelope2 && $groupAmount > 0 && $groupName) {
                        $member1 = getMemberDetailsByEnvelope($conn, $envelope1);
                        $member2 = getMemberDetailsByEnvelope($conn, $envelope2);
            
                        if ($member1 && $member2) {
                            $success = recordHarambeeTarget(
                                $conn,
                                $harambee_id,
                                'group',
                                $target,
                                $groupAmount,
                                $head_parish_id,
                                $member2['sub_parish_id'],
                                $member2['community_id'],
                                $member1['member_id'],
                                $member2['member_id'],
                                $groupName
                            );
            
                            if ($success) {
                                sendHarambeeTargetSMS($conn, $member1['member_id'], $groupAmount, $target, $harambee_id, 'group');
                                sendHarambeeTargetSMS($conn, $member2['member_id'], $groupAmount, $target, $harambee_id, 'group');
                                $results[] = [
                                    'row' => $rowIndex,
                                    'envelope_numbers' => [$envelope1, $envelope2],
                                    'group_amount' => $groupAmount,
                                    'group_name' => $groupName
                                ];
                            }
                        } else {
                            $missingMemberDetails[] = [
                                'sheet' => $sheet->getTitle(),
                                'row' => $rowIndex,
                                'envelope_number' => "$envelope1, $envelope2",
                                'target_amount' => $groupAmount
                            ];
                        }
                    }
                }
            }

        }
        
        // Create a new spreadsheet for the missing data
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        
        // Set a title before the headings
        $sheet->setCellValue('A1', 'Missing Member Details'); // Title in cell A1
        $sheet->mergeCells('A1:E1'); // Merge cells for the title to span across the columns
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14); // Make the title bold and larger font size
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER); // Center the title
        
        // Set the column headers for the missing data sheet (starting from row 2)
        $sheet->setCellValue('A2', 'Sheet')
              ->setCellValue('B2', 'Envelope Number')
              ->setCellValue('C2', 'Amount');
        
        // Fill the sheet with missing member details
        $rowNum = 3; // Start from row 3 for data to appear below the header
        foreach ($missingMemberDetails as $missing) {
            $sheet->setCellValue('A' . $rowNum, $missing['sheet']);
            $sheet->setCellValue('B' . $rowNum, $missing['envelope_number']);
            $sheet->setCellValue('C' . $rowNum, $missing['target_amount']);
            $rowNum++;
        }
        
        // Generate a filename based on the local timestamp
        $fileName = $_SERVER['DOCUMENT_ROOT'] . '/logs/missing_data_' . $local_timestamp . '.xlsx';
        
        // Write the spreadsheet to a file
        $writer = new XlsxWriter($spreadsheet);
        $writer->save($fileName);
        
        // Return the file URL in the response
        $downloadUrl = '/logs/missing_data_' . $local_timestamp . '.xlsx';

        // Return both the successful data and missing member details
        echo json_encode([
            "success" => true,
            "message" => "Harambee data uploaded successfully!",
            "data" => $results,
            "missing_member_details" => $missingMemberDetails,
            "download_url" => isset($downloadUrl) ? $downloadUrl : null // Include the URL if available
        ]);
    } catch (Exception $e) {
        echo json_encode(["success" => false, "message" => "Error processing file: " . $e->getMessage()]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

?>
