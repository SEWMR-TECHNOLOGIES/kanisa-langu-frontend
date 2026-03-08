<?php
header('Content-Type: application/json');
require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');

// Check if the database connection is successful
if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

// Ensure it's a GET request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Retrieve the required parameters
    $harambee_id = isset($_GET['harambee_id']) ? intval($_GET['harambee_id']) : null;
    $member_id = isset($_GET['member_id']) ? intval($_GET['member_id']) : null;
    $target = isset($_GET['target']) ? $_GET['target'] : null;

    // Validate input
    if (!$harambee_id) {
        echo json_encode(["success" => false, "message" => "Missing required parameter: harambee_id"]);
        exit();
    }
    
    if (!$member_id) {
        echo json_encode(["success" => false, "message" => "Missing required parameter: member_id"]);
        exit();
    }
    
    if (!$target) {
        echo json_encode(["success" => false, "message" => "Missing required parameter: target"]);
        exit();
    }

    // Determine the target table based on the 'target' parameter
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
    
    $encrypted_member_id = encryptData($member_id);
    $encrypted_harambee_id = encryptData($harambee_id);
    
    // Call the function to get individual contributions
    $contribution_result = getContributionsByDate($conn, $member_id, $harambee_id, $target_table);

    // Fetch total contributions
    $total_contribution = 0;
    $contributions = [];
    while ($row = $contribution_result->fetch_assoc()) {
        $contributions[] = $row;
        $total_contribution += $row['amount_contributed'];
    }

    // Call the function to get member target and contributions
    $memberDetails = getMemberTargetAndContributions($conn, $harambee_id, $member_id, $target);

    if ($memberDetails === false) {
        echo json_encode(["success" => false, "message" => "Invalid target or unable to fetch details"]);
        exit();
    }

    // Extract target and contribution
    $target_amount = $memberDetails['target_amount'];
    $total_contribution = $memberDetails['total_contribution'];

    // Calculate balance and percentage
    $balance = ($target_amount == 0 && $total_contribution > 0) ? 0 : abs($target_amount - $total_contribution);
    $percentage = ($target_amount > 0) ? ($total_contribution / $target_amount) * 100 : 0;
    $balance_text = ($target_amount > 0 && $total_contribution > $target_amount ) ? 'Extra'  : 'Balance';
    // Format the numbers
    $formatted_target = 'TZS ' . number_format($target_amount, 0);
    $formatted_contribution = 'TZS ' . number_format($total_contribution, 0);
    $formatted_balance = 'TZS ' . number_format($balance, 0);
    $formatted_percentage = number_format($percentage, 2) . '%';

    // Build the table for individual contributions
    $contributionTable = '
    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Payment Method</th>
                    <th style="text-align:right;">Action</th>
                </tr>
            </thead>
            <tbody>';
    
    if (count($contributions) > 0) {
    foreach ($contributions as $contribution) {
        // Reformat the contribution date to YYYY-M-d
         $formattedDate = date("Y-m-d", strtotime($contribution['contribution_date']));

        // Generate the URL with the reformatted date, encrypted IDs, and target parameter
        $receiptUrl = '/reports/member_harambee_receipt.php?member=' . urlencode($encrypted_member_id) .
                      '&harambee=' . urlencode($encrypted_harambee_id) .
                      '&date=' . urlencode($formattedDate) .
                      '&target=' . urlencode($target);


        // Create the table row
        $contributionTable .= '
        <tr>
            <td>' . htmlspecialchars(date("d M Y", strtotime($contribution['contribution_date']))) . '</td>
            <td>TZS ' . number_format($contribution['amount_contributed'], 0) . '</td>
            <td>' . htmlspecialchars($contribution['payment_method']) . '</td>
            <td style="text-align:right;"><a href="' . $receiptUrl . '" class="btn btn-primary btn-sm" target="_blank">Print Receipt</a></td> 
        </tr>';
    }
} else {
        $contributionTable .= '
        <tr>
            <td colspan="3" class="text-center">No contributions found</td>
        </tr>';
    }

    $contributionTable .= '
            </tbody>
        </table>
    </div>';

    // Add the summary row for target, total contribution, balance, and percentage
    $summaryTable = '
    <div class="table-responsive">
        <table class="table table-hover">
            <thead class="thead-dark">
                <tr>
                    <th>Target</th>
                    <th>Total Contribution</th>
                    <th>'.$balance_text.'</th>
                    <th>Percentage</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>' . $formatted_target . '</td>
                    <td>' . $formatted_contribution . '</td>
                    <td>' . $formatted_balance . '</td>
                    <td>' . $formatted_percentage . '</td>
                </tr>
            </tbody>
        </table>
    </div>';

    
    // Generate the final response with Download button
    $responseHtml = '
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title fw-semibold mb-4">Member Contributions</h5>
                ' . $contributionTable . '
            </div>
        </div>
        
        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title fw-semibold mb-4">Summary</h5>
                ' . $summaryTable . '
            </div>
        </div>
        
        <div class="text-right mt-3">
            <a target="_blank" href="/reports/member_harambee_statement.php?member=' . urlencode($encrypted_member_id) . '&harambee=' . urlencode($encrypted_harambee_id) . '&target=' . urlencode($target) . '" class="btn btn-success">
                <i class="fas fa-file-pdf"></i> Download Report
            </a>
        </div>';


    // Return the HTML response
    echo json_encode([
        "success" => true,
        "html" => $responseHtml
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
