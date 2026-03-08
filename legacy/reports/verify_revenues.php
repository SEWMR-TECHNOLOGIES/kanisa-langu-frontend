<?php
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/libraries/vendor/autoload.php');

use Dompdf\Dompdf;

// Instantiate Dompdf
$dompdf = new Dompdf();

// Set up the options
$options = $dompdf->getOptions();
$options->setFontCache('fonts');
$options->set('isRemoteEnabled', true);
$options->setChroot([$_SERVER['DOCUMENT_ROOT'] . '/assets/fonts/']);
$dompdf->setOptions($options);

$imageData = base64_encode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/assets/images/logos/kkkt-logo.jpg'));
$src = 'data:image/jpeg;base64,' . $imageData;

// Set the timezone to Africa/Nairobi
date_default_timezone_set('Africa/Nairobi');

// Generate the timestamp
$timestamp = date('l, F j, Y g:i A'); 

// Initialize variables from GET parameters
$target = isset($_GET['target']) ? $_GET['target'] : null;
$revenue_date = isset($_GET['revenue_date']) ? $_GET['revenue_date'] : null;
$sub_parish_id = isset($_GET['sub_parish_id']) ? $_GET['sub_parish_id'] : null;
$community_id = isset($_GET['community_id']) ? $_GET['community_id'] : null;
$group_id = isset($_GET['group_id']) ? $_GET['group_id'] : null;

// Validate revenue date
if (empty($revenue_date)) {
    header("Location: /error.php?message=" . urlencode("Revenue date is required."));
    exit();
}

// Process each ID
$sub_parish_id = processId($sub_parish_id, "Sub Parish");
$community_id = processId($community_id, "Community");
$group_id = processId($group_id, "Group");

// Call validation function
validateTarget($target, $sub_parish_id, $community_id, $group_id);

if(!isset($_SESSION['head_parish_id'])){
     header("Location: /error.php?message=" . urlencode("Unauthorized"));
    exit();
}
$head_parish_id = $_SESSION['head_parish_id'];
$parish_info = getParishInfo($conn, $head_parish_id);

// Determine the target table based on the 'target' parameter
$target_table = '';
switch ($target) {
    case 'head-parish':
        $revenue_table = 'head_parish_revenues';
        $other_head_parish_revenues = 'other_head_parish_revenues';
        break;
    case 'sub-parish':
        $revenue_table = 'sub_parish_revenues';
        break;
    case 'community':
        $revenue_table = 'community_revenues';
        break;
    case 'groups':
        $revenue_table = 'group_revenues';
        break;
    default:
        echo json_encode(["success" => false, "message" => "Invalid revenue target type provided"]);
        exit();
}

// Convert revenue_date to DateTime object
$revenueDateObj = new DateTime($revenue_date);

// Get Monday of the current week
$monday = clone $revenueDateObj;
$monday->modify('Monday this week');

// Get Sunday of the current week
$sunday = clone $monday;
$sunday->modify('Sunday this week');

// Format the date range
$mondayFormatted = $monday->format('Y-m-d');
$sundayFormatted = $sunday->format('Y-m-d');

// Echo the date range
// echo "Week Range: $mondayFormatted to $sundayFormatted";

// Initialize an array to hold results
$revenues = [];

// Prepare SQL query based on target
$query = "";
$params = [];

if ($target === 'head-parish') {
    // Fetch from both head_parish_revenues and other_head_parish_revenues
    $query = "
        (SELECT 'head_parish' AS source, revenue_id, revenue_stream_id, head_parish_id, service_number, 
                revenue_amount, payment_method, recorded_by, date_recorded, revenue_date, description
         FROM head_parish_revenues
         WHERE head_parish_id = ? AND revenue_date BETWEEN ? AND ?)
        UNION ALL
        (SELECT 'other_head_parish' AS source, revenue_id, revenue_stream_id, head_parish_id, service_number, 
                revenue_amount, payment_method, recorded_by, date_recorded, revenue_date, description
         FROM other_head_parish_revenues
         WHERE head_parish_id = ? AND revenue_date BETWEEN ? AND ?)
    ";
    $params = [$head_parish_id, $mondayFormatted, $sundayFormatted, $head_parish_id, $mondayFormatted, $sundayFormatted];

} elseif ($target === 'sub-parish') {
    $query = "
        SELECT revenue_id, revenue_stream_id, head_parish_id, sub_parish_id, revenue_amount, 
               payment_method, recorded_by, date_recorded, revenue_date, description
        FROM sub_parish_revenues
        WHERE head_parish_id = ? AND sub_parish_id = ? AND revenue_date BETWEEN ? AND ?
    ";
    $params = [$head_parish_id, $sub_parish_id, $mondayFormatted, $sundayFormatted];

} elseif ($target === 'community') {
    $query = "
        SELECT revenue_id, revenue_stream_id, head_parish_id, sub_parish_id, community_id, revenue_amount, 
               payment_method, recorded_by, date_recorded, revenue_date, description
        FROM community_revenues
        WHERE head_parish_id = ? AND sub_parish_id = ? AND community_id = ? AND revenue_date BETWEEN ? AND ?
    ";
    $params = [$head_parish_id, $sub_parish_id, $community_id, $mondayFormatted, $sundayFormatted];

} elseif ($target === 'groups') {
    $query = "
        SELECT revenue_id, revenue_stream_id, head_parish_id, group_id, revenue_amount, 
               payment_method, recorded_by, date_recorded, revenue_date, description
        FROM group_revenues
        WHERE head_parish_id = ? AND group_id = ? AND revenue_date BETWEEN ? AND ?
    ";
    $params = [$head_parish_id, $group_id, $mondayFormatted, $sundayFormatted];
} else {
    echo json_encode(["success" => false, "message" => "Invalid revenue target type provided"]);
    exit();
}

// Prepare and bind the query
$stmt = $conn->prepare($query);

// Dynamically bind parameters
$types = str_repeat("s", count($params)); 
$stmt->bind_param($types, ...$params);

// Execute the statement
$stmt->execute();

// Get the result set
$result = $stmt->get_result();

// Fetch all records
while ($row = $result->fetch_assoc()) {
    $revenues[] = $row;
}

// Close the statement and connection
$stmt->close();
$conn->close();

// Return the data as JSON
// echo json_encode([
//     "success" => true,
//     "week_range" => "$mondayFormatted to $sundayFormatted",
//     "revenues" => $revenues
// ]);

// Generate the Report HTML
// $htm = '<html><head>';
// $htm .= '<style>
//             body { font-family: Arial, sans-serif; }
//             .header { text-align: center; margin-bottom: 20px; }
//             .logo { width: 80px; height: auto; }
//             table { width: 100%; border-collapse: collapse; }
//             th, td { border: 1px solid black; padding: 8px; text-align: left; }
//             th { background-color: #f2f2f2; }
//         </style>';
// $htm .= '</head><body>';

// // Logo and Title
// $htm .= '<div class="header">';
// $htm .= '<img class="logo" src="data:image/jpeg;base64,' . base64_encode(file_get_contents($_SERVER['DOCUMENT_ROOT'] . '/assets/images/logos/kkkt-logo.jpg')) . '"/>';
// $htm .= '<h2>Revenue Report</h2>';
// $htm .= '<h4>' . $parish_info['name'] . '</h4>';
// $htm .= '<p>Generated on: ' . $timestamp . '</p>';
// $htm .= '<p>Week Range: ' . $mondayFormatted . ' to ' . $sundayFormatted . '</p>';
// $htm .= '</div>';

// // Revenue Table
// $htm .= '<table>';
// $htm .= '<tr>
//             <th>#</th>
//             <th>Revenue Stream</th>
//             <th>Amount</th>
//             <th>Payment Method</th>
//             <th>Recorded By</th>
//             <th>Revenue Date</th>
//             <th>Description</th>
//         </tr>';

// $totalRevenue = 0;
// foreach ($revenues as $index => $revenue) {
//     $totalRevenue += $revenue['revenue_amount'];
//     $htm .= '<tr>
//                 <td>' . ($index + 1) . '</td>
//                 <td>' . htmlspecialchars($revenue['revenue_stream_id']) . '</td>
//                 <td>' . number_format($revenue['revenue_amount'], 2) . '</td>
//                 <td>' . htmlspecialchars($revenue['payment_method']) . '</td>
//                 <td>' . htmlspecialchars($revenue['recorded_by']) . '</td>
//                 <td>' . htmlspecialchars($revenue['revenue_date']) . '</td>
//                 <td>' . htmlspecialchars($revenue['description']) . '</td>
//             </tr>';
// }

// $htm .= '<tr>
//             <td colspan="2"><strong>Total Revenue:</strong></td>
//             <td colspan="5"><strong>' . number_format($totalRevenue, 2) . '</strong></td>
//         </tr>';
// $htm .= '</table>';

// // Footer
// $htm .= '<p style="text-align: center; margin-top: 20px;">&copy; ' . date("Y") . ' Kanisa Langu - SEWMR Technologies</p>';
// $htm .= '</body></html>';

// // Load HTML to DOMPDF
// $dompdf->loadHtml($htm);

// // Set Paper Size
// $dompdf->setPaper('A4', 'portrait');

// // Render PDF
// $dompdf->render();

// // Output PDF
// $dompdf->stream("revenue_report.pdf", ["Attachment" => false]);

?>