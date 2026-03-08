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
    // Validate date
    $date = isset($_POST['date']) ? $_POST['date'] : null;
    $local_timestamp = isset($_POST['local_timestamp']) ? $conn->real_escape_string($_POST['local_timestamp']) : null; 
    if (empty($date)) {
        echo json_encode(["success" => false, "message" => "Date is required"]);
        exit();
    }

    // Check if a file is uploaded
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
                // Skip the header row (if applicable)
                if ($rowIndex === 1) {
                    continue;
                }

                // Envelope Number is in Column A
                $envelopeNumber = isset($row['A']) ? trim($row['A']) : null;
                // Amount is in Column B
                $amount = isset($row['B']) ? $row['B'] : null;
                // Payment method is in Column C
                $paymentMethod = isset($row['C']) && !empty($row['C']) ? $row['C'] : 'Cash'; 

                // Remove commas from amount and convert to float
                if (!is_null($amount)) {
                    $amount = floatval(str_replace(',', '', $amount));
                }

                // Exclude rows with empty or zero amounts
                if (!is_null($envelopeNumber) && $amount > 0) {
                    // Get member details using the envelope number
                    $memberDetails = getMemberDetailsByEnvelope($conn, $envelopeNumber);

                    // If member details are found, add to results
                    if ($memberDetails) {
                        $member_id = $memberDetails['member_id'];
                        $sub_parish_id = $memberDetails['sub_parish_id'];
                        $community_id = $memberDetails['community_id'];

                        // Prepare the data to be recorded
                        $recordData = [
                            'sheet' => $sheet->getTitle(),
                            'row' => $rowIndex,
                            'date' => $date, 
                            'envelope_number' => $envelopeNumber,
                            'amount' => $amount,
                            'payment_method' => $paymentMethod,
                            'member_id' => $member_id,
                            'sub_parish_id' => $sub_parish_id,
                            'community_id' => $community_id,
                            'head_parish_id' => $head_parish_id,
                            'recorded_by' => $_SESSION['head_parish_admin_id'],
                            'local_timestamp' => $local_timestamp
                        ];

                        // Call the function to record the envelope data
                        $success = recordEnvelopeData($conn, $member_id, $amount, $date, $paymentMethod, $head_parish_id, $sub_parish_id, $community_id, $local_timestamp);

                        if ($success) {
                            // If successful, add to results
                            $results[] = $recordData;
                        } else {
                            // If failed, add to the missing member details
                            $missingMemberDetails[] = $recordData;
                        }
                    } else {
                        // If no member details are found, push this data to missingMemberDetails
                        $missingMemberDetails[] = [
                            'sheet' => $sheet->getTitle(),
                            'row' => $rowIndex,
                            'date' => $date, // Include date from POST
                            'envelope_number' => $envelopeNumber,
                            'amount' => $amount,
                            'payment_method' => $paymentMethod
                        ];
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
              ->setCellValue('B2', 'Date')
              ->setCellValue('C2', 'Envelope Number')
              ->setCellValue('D2', 'Amount')
              ->setCellValue('E2', 'Payment Method');
        
        // Fill the sheet with missing member details
        $rowNum = 3; // Start from row 3 for data to appear below the header
        foreach ($missingMemberDetails as $missing) {
            $sheet->setCellValue('A' . $rowNum, $missing['sheet']);
            $sheet->setCellValue('B' . $rowNum, $missing['date']);
            $sheet->setCellValue('C' . $rowNum, $missing['envelope_number']);
            $sheet->setCellValue('D' . $rowNum, $missing['amount']);
            $sheet->setCellValue('E' . $rowNum, $missing['payment_method']);
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
            "message" => "Envelope data uploaded successfully!",
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
