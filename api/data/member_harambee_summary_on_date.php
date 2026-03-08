<?php 
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');

// Initialize variables from GET parameters
$harambee_id = isset($_GET['harambee_id']) ? $_GET['harambee_id'] : null;
$target = isset($_GET['target']) ? $_GET['target'] : null;
$from_date = isset($_GET['from_date']) ? $_GET['from_date'] : date('Y-m-d');
$to_date = isset($_GET['to_date']) ? $_GET['to_date'] : null;
$sub_parish_id = isset($_GET['sub_parish_id']) ? $_GET['sub_parish_id'] : date('Y-m-d');

// Validate harambee_id
if (empty($harambee_id) || !preg_match('/^[a-zA-Z0-9]+$/', $harambee_id)) {
    echo json_encode(["success" => false, "message" => "Invalid harambee ID."]);
    exit;
}

// Validate target
$valid_targets = ['head-parish', 'sub-parish', 'community', 'groups'];
if (!in_array($target, $valid_targets)) {
    echo json_encode(["success" => false, "message" => "Invalid target type provided."]);
    exit;
}

// Validate from_date
if (!strtotime($from_date)) {
    echo json_encode(["success" => false, "message" => "Invalid contribution date format."]);
    exit;
}

// Validate to_date if provided
if ($to_date !== null) {
    if (!strtotime($to_date)) {
        echo json_encode(["success" => false, "message" => "Invalid to_date format."]);
        exit;
    }
    // Ensure to_date is not before from_date
    if (strtotime($to_date) < strtotime($from_date)) {
        echo json_encode(["success" => false, "message" => "'to_date' must be greater than or equal to 'from_date'."]);
        exit;
    }
}


// Validate harambee_id
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
// Get harambee details
$harambee_details = get_harambee_details($conn, $harambee_id, $target);
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

// Get IDs for memmber contributed on date
$on_date_member_ids = getHarambeeMemberIdsByContributionDate($conn, $harambee_id, $target, $from_date, $to_date);


// Initialize an array to store member details and a set to track processed group names
$on_date_member_details_array = [];
$on_date_processed_groups = [];

// MEMBERS ON DATE

foreach ($on_date_member_ids as $member_id) {
    // Get the member details
    $member = getSingleMemberHarambeeDetails($conn, $member_id, $harambee_id, $target);
    
    // If sub_parish_id is provided, filter members based on it
    if ($sub_parish_id && $member['sub_parish_id'] != $sub_parish_id) {
        continue; // Skip this member if their sub_parish_id does not match
    }
    
    // Check if the member belongs to a group
    if ($member['group_name'] != null) {
        // If this group has already been processed, skip this member
        if (in_array($member['group_name'], $on_date_processed_groups)) {
            continue; // Skip the current iteration
        }
        // Add the group name to the processed groups set
        $full_name = $member['group_name'];
        $on_date_processed_groups[] = $member['group_name'];
    } else {
        // If the member is not part of a group, use their individual full name
        $full_name = getMemberFullName($member);
    }
    
    // Get contributions by date (or date range)
    $contribution_result =  getTotalContributionsBetweenDates($conn, $harambee_id, $member_id, $from_date, $to_date, $target_table);
    if ($contribution_result === false) {
        echo json_encode(["success" => false, "message" => "Invalid target or unable to fetch details"]);
        exit();
    }
    // Extract contribution amount before and after
    $amount_contributed_before_date = $contribution_result['total_before_date'];
    $amount_contributed_on_date = $contribution_result['on_date_contributions'];
    $total_contributed_up_to_date = $contribution_result['total_contributed'];
    

	// Get member target and contributions
    $memberDetails = getMemberTargetAndContributions($conn, $harambee_id, $member_id, $target);
    
    if ($memberDetails === false) {
        echo json_encode(["success" => false, "message" => "Invalid target or unable to fetch details"]);
        exit();
    }

    // Extract target and contribution amounts
    $target_amount = $memberDetails['target_amount'];
    $total_contribution = $memberDetails['total_contribution'];
    $balance = $target_amount - $total_contributed_up_to_date;
    $balance = ($target_amount > 0) ? ($target_amount - $total_contributed_up_to_date) : 0;
    $percentage = $target_amount > 0 ? calculatePercentage($total_contributed_up_to_date, $target_amount) : 0.00;

    // Store details in the array
    $on_date_member_details_array[] = [
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
        'target' => $target_amount,
        'contribution' => $total_contribution, // not based on date but full amount (wont be used mut let it remain)
        'amount_contributed_before_date' => $amount_contributed_before_date,
        'amount_contributed_on_date' => $amount_contributed_on_date,
        'total_contributed_up_to_date' => $total_contributed_up_to_date,
        'balance' => $balance,
        'percentage' => $percentage
    ];
}


// DISTRIBUTED AMOUNT FOR THAT SUB PARISH
$sub_parish_distributed_amount = getDistributedAmount($conn, $target, $sub_parish_id, $harambee_id);

// Initialize sub-parish wise storage - ON DATE
$on_date_harambee_data = [];
$on_date_sub_parishes_data = [];

// Iterate over the member details to build the grouped structure
foreach ($on_date_member_details_array as $member) {
    $sub_parish_id = $member['sub_parish_id'];
    $sub_parish_name = $member['sub_parish_name'];
    $sub_parish_distributed_amount = $sub_parish_distributed_amount;
    $community_id = $member['community_id'];
    $community_name = $member['community_name'];

    // Initialize sub-parish if not already present
    if (!isset($on_date_sub_parishes_data[$sub_parish_id])) {
        $on_date_sub_parishes_data[$sub_parish_id] = [
            'sub_parish_id' => $sub_parish_id,
            'sub_parish_name' => $sub_parish_name,
            'distributed_amount' => $sub_parish_distributed_amount,
            'communities' => []
        ];
    }

    // Initialize community data within the sub-parish
    if (!isset($on_date_sub_parishes_data[$sub_parish_id]['communities'][$community_id])) {
        $on_date_sub_parishes_data[$sub_parish_id]['communities'][$community_id] = [
            'community_id' => $community_id,
            'community_name' => $community_name,
            'total_before_date' => 0,
            'total_on_date' => 0,
            'total_contributed_up_to_date' => 0,
            'member_count' => 0  // Initialize member count
        ];
    }

    // Sum the contributions for this community
    $on_date_sub_parishes_data[$sub_parish_id]['communities'][$community_id]['total_before_date'] += $member['amount_contributed_before_date'];
    $on_date_sub_parishes_data[$sub_parish_id]['communities'][$community_id]['total_on_date'] += $member['amount_contributed_on_date'];
    $on_date_sub_parishes_data[$sub_parish_id]['communities'][$community_id]['total_contributed_up_to_date'] += $member['total_contributed_up_to_date'];

    // Increment the member count for this community
    $on_date_sub_parishes_data[$sub_parish_id]['communities'][$community_id]['member_count']++;
}

// Initialize grand total variables
$grand_total_before_date = 0;
$grand_total_on_date = 0;
$grand_total_contributed_up_to_date = 0;
$grand_member_count = 0;  // Initialize grand member count

// Iterate over the sub-parishes to calculate grand totals and member count
foreach ($on_date_sub_parishes_data as $sub_parish) {  // Remove the reference (`&`)
    foreach ($sub_parish['communities'] as $community) {  // Remove the reference (`&`)
        // Sum the contributions for this community
        $grand_total_before_date += $community['total_before_date'];
        $grand_total_on_date += $community['total_on_date'];
        $grand_total_contributed_up_to_date += $community['total_contributed_up_to_date'];

        // Sum the member counts for this community
        $grand_member_count += $community['member_count'];
    }

    // Reindex communities to structure for JSON output (to reset array keys)
    $sub_parish['communities'] = array_values($sub_parish['communities']);
}


// Prepare final response data
$on_date_harambee_data['sub_parishes'] = array_values($on_date_sub_parishes_data);



// ********************************************************************************************************* //

// ALL MEMBERS 
// Get all member IDs
$all_member_ids = getHarambeeMemberIds($conn, $harambee_id, $target);
// Initialize an array to store member details and a set to track processed group names
$all_member_details_array = [];
$all_members_processed_groups = [];

foreach ($all_member_ids as $member_id) {
    // Get the member details
    $member = getSingleMemberHarambeeDetails($conn, $member_id, $harambee_id, $target);
    
    // If sub_parish_id is provided, filter members based on it
    if ($sub_parish_id && $member['sub_parish_id'] != $sub_parish_id) {
        continue; // Skip this member if their sub_parish_id does not match
    }
    
    // Check if the member belongs to a group
    if ($member['group_name'] != null) {
        // If this group has already been processed, skip this member
        if (in_array($member['group_name'], $all_members_processed_groups)) {
            continue; // Skip the current iteration
        }
        // Add the group name to the processed groups set
        $full_name = $member['group_name'];
        $all_members_processed_groups[] = $member['group_name'];
    } else {
        // If the member is not part of a group, use their individual full name
        $full_name = getMemberFullName($member);
    }
    
 
	// Get member target and contributions
    $memberDetails = getMemberTargetAndContributions($conn, $harambee_id, $member_id, $target);
    
    if ($memberDetails === false) {
        echo json_encode(["success" => false, "message" => "Invalid target or unable to fetch details"]);
        exit();
    }

    // Extract target and contribution amounts
    $target_amount = $memberDetails['target_amount'];
    $total_contribution = $memberDetails['total_contribution'];
    $balance = $target_amount - $total_contribution;
    $balance = ($target_amount > 0) ? ($target_amount - $total_contribution) : 0;
    $percentage = $target_amount > 0 ? calculatePercentage($total_contribution, $target_amount) : 0.00;

    // Store details in the array
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
        'target' => $target_amount,
        'contribution' => $total_contribution, 
        'balance' => $balance,
        'percentage' => $percentage
    ];
}


$total_harambee_contribution = getTotalContributionBySubParishFromArray($all_member_details_array, $sub_parish_id);


// ***************************************************************************************************************************************************

// Start generating the HTML content
echo "<h2>Harambee Contribution Summary</h2>";

// 1. Summary Table (Leave this blank for now, just a placeholder)
echo "<h3>Summary</h3>";
echo "<table border='1' cellpadding='5' cellspacing='0'>
        <thead>
            <tr>
                <th>UWIANO</th>
                <th>TASLIMU YA AWALI</th>
                <th>TASLIMU YA WIKI</th>
                <th>TASLIMU KUU</th>
                <th>SALIO KUU</th>
                <th>IDADI</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>" . number_format($sub_parish_distributed_amount, 2) . "</td>
                <td>" . number_format($total_harambee_contribution - $grand_total_on_date, 2) . "</td>
                <td>" . number_format($grand_total_on_date, 2) . "</td>
                <td>" . number_format($total_harambee_contribution, 2) . "</td>
                <td>" . number_format($sub_parish_distributed_amount - $total_harambee_contribution, 2) . "</td>
                <td>" . $grand_member_count . "</td>
            </tr>
        </tbody>
    </table>";

// 2. Communities Table - Divided into Two Columns with Serial Numbers
echo "<h3>Communities Contribution Details</h3>";
echo "<table border='1' cellpadding='5' cellspacing='0'>
        <thead>
            <tr>
                <th>#</th> <!-- Serial Number Column -->
                <th>JUMUIYA</th>
                <th>IDADI</th>
                <th>TASLIMU</th> <!-- Count of Contributions -->
                <th>#</th> <!-- Serial Number Column -->
                <th>JUMUIYA Name</th>
                <th>IDADI</th>
                <th>TASLIMU</th> <!-- Count of Contributions -->
            </tr>
        </thead>
        <tbody>";

// Prepare community data in two columns
$communityData = [];
foreach ($on_date_sub_parishes_data as $sub_parish) {
    foreach ($sub_parish['communities'] as $community) {
        $communityData[] = [
            'name' => htmlspecialchars($community['community_name']),
            'total_contributions' => number_format($community['total_on_date'], 2),
            'contribution_count' => $community['member_count'] // Add the count of contributions
        ];
    }
}

// Split community data into two columns
$columns = array_chunk($communityData, ceil(count($communityData) / 2));

$maxRows = max(count($columns[0]), count($columns[1]));
$serialNumber = 1; // Initialize serial number

for ($i = 0; $i < $maxRows; $i++) {
    echo "<tr>";
    // First column community data
    if (isset($columns[0][$i])) {
        echo "<td>" . $serialNumber++ . "</td>"; // Serial number for first column
        echo "<td>" . $columns[0][$i]['name'] . "</td>";
        echo "<td>" . $columns[0][$i]['contribution_count'] . "</td>"; 
        echo "<td>" . $columns[0][$i]['total_contributions'] . "</td>";
    } else {
        echo "<td></td><td></td><td></td><td></td>"; // Empty cells if there's no data in this column
    }
    // Second column community data
    if (isset($columns[1][$i])) {
        echo "<td>" . $serialNumber++ . "</td>"; // Serial number for second column
        echo "<td>" . $columns[1][$i]['name'] . "</td>";
        echo "<td>" . $columns[1][$i]['contribution_count'] . "</td>";
        echo "<td>" . $columns[1][$i]['total_contributions'] . "</td>";
    } else {
        echo "<td></td><td></td><td></td><td></td>"; // Empty cells if there's no data in this column
    }
    echo "</tr>";
}

echo "  </tbody>
    </table>";



// 3. Members Details Table
echo "<h3>Members Contribution Details</h3>";
echo "<table border='1' cellpadding='5' cellspacing='0'>
        <thead>
            <tr>
                <th>Name</th>
                <th>Target Amount</th>
                <th>Contribution Before Date</th>
                <th>Contribution On Date</th>
                <th>Total Contribution Up to Date</th>
                <th>Balance</th>
                <th>Percentage</th>
            </tr>
        </thead>
        <tbody>";

// Displaying member contribution details
foreach ($on_date_member_details_array as $member) {
    echo "<tr>
            <td>" . htmlspecialchars($member['name']) . "</td>
            <td>" . number_format($member['target'], 2) . "</td>
            <td>" . number_format($member['amount_contributed_before_date'], 2) . "</td>
            <td>" . number_format($member['amount_contributed_on_date'], 2) . "</td>
            <td>" . number_format($member['total_contributed_up_to_date'], 2) . "</td>
            <td>" . ($member['balance'] < 0 ? "+" : "") . number_format(abs($member['balance'])) . "</td>
            <td>" . number_format($member['percentage'], 2) . "%</td>
          </tr>";
}


echo "  </tbody>
    </table>";
    
    

 ?>