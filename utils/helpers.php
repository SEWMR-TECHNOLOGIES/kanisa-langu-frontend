<?php
require_once(__DIR__ . '/encryption_functions.php');

function getHeadParishSecretaryPhone($conn) {
    if (!isset($_SESSION['head_parish_id'])) return false;

    $head_parish_id = $_SESSION['head_parish_id'];

    $query = "SELECT head_parish_admin_phone FROM head_parish_admins WHERE head_parish_id = ? AND head_parish_admin_role = 'secretary' LIMIT 1";
    $stmt = $conn->prepare($query);
    if (!$stmt) return false;

    $stmt->bind_param("i", $head_parish_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $stmt->close();

    if ($row = $result->fetch_assoc()) {
        $phone = $row['head_parish_admin_phone'];
        if (strpos($phone, '255') === 0) $phone = '0' . substr($phone, 3);
        return $phone;
    }

    return false;
}

function render_payment_method_dropdown($id) {
    $payment_methods = [
        'Cash' => 'Cash',
        'Bank Transfer' => 'Bank Transfer',
        'Mobile Payment' => 'Mobile Payment',
        'Card' => 'Card'
    ];

    echo '<select class="form-select" id="' . $id . '" name="payment_method">';
    foreach ($payment_methods as $key => $value) {
        echo '<option value="' . $key . '">' . $value . '</option>';
    }
    echo '</select>';
}

function getUniqueYearsFromEnvelopeTargets($conn) {
    $years = [];

    $sql = "SELECT DISTINCT YEAR(from_date) AS year FROM envelope_targets 
            UNION 
            SELECT DISTINCT YEAR(end_date) AS year FROM envelope_targets 
            ORDER BY year ASC";

    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $years[] = $row['year'];
        }
        return ["success" => true, "years" => $years];
    }

    return ["success" => false, "message" => "No years found."];
}

function render_harambee_responsibility_dropdown($id, $name) {
    $responsibilities = [
        'M/Kiti - Mtaa' => 'M/Kiti - Mtaa',
        'M/Kiti - Kamati' => 'M/Kiti - Kamati',
        'M/M/Kiti' => 'M/M/Kiti',
        'Katibu' => 'Katibu',
        'M/Hazina' => 'M/Hazina',
        'Mjumbe' => 'Mjumbe'
    ];

    echo '<select class="form-select" id="' . $id . '" name="' . $name . '">';
    echo '<option value="">Select Responsibility</option>';
    foreach ($responsibilities as $key => $value) {
        echo '<option value="' . $key . '">' . $value . '</option>';
    }
    echo '</select>';
}


function generateAlphanumericOTP($length = 5) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz';
    $otp = '';
    for ($i = 0; $i < $length; $i++) {
        $otp .= $characters[random_int(0, strlen($characters) - 1)];
    }
    return $otp;
}

function getEnvelopeTargetAmount($conn, $member_id) {
    $from_date = date('Y-01-01');
    $end_date = date('Y-12-31');

    $query = "SELECT target as target_amount FROM envelope_targets WHERE member_id = ? AND from_date = ? AND end_date = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("iss", $member_id, $from_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        return $row['target_amount'];
    }

    return null;
}

function get_account_id_by_revenue_stream($conn, $revenue_stream_id, $target) {
    $table = '';
    $id_column = '';

    switch ($target) {
        case 'head_parish':
            $table = 'head_parish_revenue_streams';
            $id_column = 'head_parish_id';
            break;
        case 'diocese':
            $table = 'diocese_revenue_streams';
            $id_column = 'diocese_id';
            break;
        case 'province':
            $table = 'province_revenue_streams';
            $id_column = 'province_id';
            break;
        default:
            return false;
    }

    $sql = "SELECT account_id FROM $table WHERE revenue_stream_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $revenue_stream_id);

    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            return $row['account_id'];
        }
    }

    return false;
}


function get_harambee_details($conn, $harambee_id, $target) {
    // Define the table and other details based on the target type
    $table = '';
    
    switch ($target) {
        case 'head_parish':
            $table = 'head_parish_harambee';
            break;
        case 'head-parish':
            $table = 'head_parish_harambee';
            break;
        case 'sub_parish':
            $table = 'sub_parish_harambee';
            break;
        case 'sub-parish':
            $table = 'sub_parish_harambee';
            break;
        case 'community':
            $table = 'community_harambee';
            break;
        case 'groups':
            $table = 'groups_harambee';
            break;
        default:
            return false; // Invalid target
    }

    // Prepare the query to get the details
    $sql = "SELECT description, from_date, to_date, amount 
            FROM $table 
            WHERE harambee_id = ?";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $harambee_id);

    // Execute and fetch results
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            // Fetch the first row (since harambee_id is unique)
            $row = $result->fetch_assoc();
            return [
                'description' => $row['description'],
                'from_date'   => $row['from_date'],
                'to_date'     => $row['to_date'],
                'amount'      => $row['amount']
            ];
        }
    }

    // Return false if the query fails or no data is found
    return false;
}


function getSubParishName($subParishId, $conn) {
    // Prepare the SQL query
    $query = "SELECT sub_parish_name FROM sub_parishes WHERE sub_parish_id = ?";
    
    // Prepare the statement
    if ($stmt = $conn->prepare($query)) {
        // Bind the parameter
        $stmt->bind_param("i", $subParishId);
        
        // Execute the statement
        $stmt->execute();
        
        // Bind result variables
        $stmt->bind_result($subParishName);
        
        // Fetch the result
        if ($stmt->fetch()) {
            // Return the sub_parish_name fully capitalized
            return strtoupper($subParishName);
        } else {
            // If no result is found, return an empty string
            return '';
        }
        
        // Close the statement
        $stmt->close();
    } else {
        return ''; // Return empty string if there was an error preparing the statement
    }
}


function getCommunityName($communityId, $conn) {
    // Prepare the SQL query
    $query = "SELECT community_name FROM communities WHERE community_id = ?";
    
    // Prepare the statement
    if ($stmt = $conn->prepare($query)) {
        // Bind the parameter
        $stmt->bind_param("i", $communityId);
        
        // Execute the statement
        $stmt->execute();
        
        // Bind result variables
        $stmt->bind_result($communityName);
        
        // Fetch the result
        if ($stmt->fetch()) {
            // Return the sub_parish_name fully capitalized
            return strtoupper($communityName);
        } else {
            // If no result is found, return an empty string
            return '';
        }
        
        // Close the statement
        $stmt->close();
    } else {
        return ''; // Return empty string if there was an error preparing the statement
    }
}


function getGroupName($groupId, $conn) {
    $query = "SELECT group_name FROM groups WHERE group_id = ?";

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $groupId);
        $stmt->execute();
        $stmt->bind_result($groupName);

        if ($stmt->fetch()) {
            $stmt->close();
            return strtoupper($groupName);
        }

        $stmt->close();
        return '';
    }

    return '';
}


function update_account_balance($conn, $account_id, $amount, $target) {
    $table = '';

    switch ($target) {
        case 'head_parish':
            $table = 'head_parish_bank_accounts';
            break;
        case 'diocese':
            $table = 'diocese_bank_accounts';
            break;
        case 'province':
            $table = 'province_bank_accounts';
            break;
        default:
            return false; // Invalid target
    }

    // Update balance, adding the amount (or subtracting if needed)
    $sql = "UPDATE $table SET balance = balance + ? WHERE account_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("di", $amount, $account_id);

    if ($stmt->execute()) {
        return true; 
    }
    return false; 
}

function getHeadParishSmsInfo($conn, $head_parish_id) {
    $sql = "SELECT account_name, api_username, api_password, api_token, sender_id
            FROM head_parish_sms_api_info 
            WHERE head_parish_id = ? 
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    if (!$stmt) return false;

    $stmt->bind_param("i", $head_parish_id);
    $stmt->execute();
    $stmt->bind_result($account_name, $api_username, $encrypted_password, $encrypted_token, $sender_id);
    $stmt->fetch();
    $stmt->close();

    if (empty($api_username) || empty($encrypted_password) || empty($account_name)) {
        return false; // No valid credentials
    }

    $api_password = decryptData($encrypted_password); // Decrypt password
    $api_token    = !empty($encrypted_token) ? decryptData($encrypted_token) : null; // Decrypt token if exists

    return [
        'account_name' => $account_name,
        'username'     => $api_username,
        'password'     => $api_password,
        'api_token'    => $api_token,
        'sender_id'    => $sender_id
    ];
}

function get_sms_credentials($conn, $head_parish_id) {
    $sql = "SELECT account_name, api_username, api_password, api_token, sender_id
            FROM head_parish_sms_api_info 
            WHERE head_parish_id = ? 
            LIMIT 1";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $head_parish_id);

    $stmt->execute();
    $stmt->bind_result($account_name, $api_username, $encrypted_password, $encrypted_token, $sender_id);
    $stmt->fetch();
    $stmt->close();

    if (empty($api_username) || empty($encrypted_password) || empty($account_name)) {
        return false; // No credentials found
    }

    $api_password = decryptData($encrypted_password);
    $api_token    = !empty($encrypted_token) ? decryptData($encrypted_token) : null;

    return [
        'account_name' => $account_name,
        'username'     => $api_username,
        'password'     => $api_password,
        'api_token'    => $api_token,
        'sender_id'    => $sender_id
    ];
}



function getSubParishAndCommunity($member_id, $conn) {
    $sql = "SELECT sub_parish_id, community_id FROM church_members WHERE member_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc() ?: null;
}


function getTargetDetails($conn, $harambee_id, $member_id, $target_table) {
    $target_query = "
        SELECT 
            t.sub_parish_id, 
            sp.sub_parish_name, 
            t.community_id, 
            c.community_name, 
            t.harambee_committee_responsibility, 
            t.target_type, 
            t.target as target_amount 
        FROM $target_table t
        JOIN sub_parishes sp ON t.sub_parish_id = sp.sub_parish_id
        JOIN communities c ON t.community_id = c.community_id
        WHERE t.harambee_id = ? AND t.member_id = ?
    ";
    
    $stmt = $conn->prepare($target_query);
    $stmt->bind_param("ii", $harambee_id, $member_id);
    $stmt->execute();
    return $stmt->get_result();
}

function getHarambeeGroupDetails($conn, $harambee_id, $member_id, $target) {
    // Define the tables based on the target type
    switch ($target) {
        case 'head-parish':
            $member_table = "head_parish_harambee_group_members";
            $group_table = "head_parish_harambee_groups";
            $harambee_table = "head_parish_harambee";
            break;
        case 'sub-parish':
            $member_table = "sub_parish_harambee_group_members";
            $group_table = "sub_parish_harambee_groups";
            $harambee_table = "sub_parish_harambee";
            break;
        case 'community':
            $member_table = "community_harambee_group_members";
            $group_table = "community_harambee_groups";
            $harambee_table = "community_harambee";
            break;
        case 'groups':
        case 'group':
            $member_table = "groups_harambee_group_members";
            $group_table = "groups_harambee_groups";
            $harambee_table = "groups_harambee";
            break;
        default:
            return false; // Invalid target
    }

    // Prepare query to check if the member exists in the specified table and get group and Harambee details
    $query = "
        SELECT 
            g.harambee_group_id, 
            g.harambee_group_name, 
            g.harambee_group_target,
            DATE(g.date_created) as date_created,  -- Include date_created
            h.description AS harambee_description
        FROM $member_table m
        JOIN $group_table g ON m.harambee_group_id = g.harambee_group_id
        JOIN $harambee_table h ON g.harambee_id = h.harambee_id
        WHERE m.member_id = ? AND g.harambee_id = ?
        LIMIT 1
    ";

    // Execute the prepared statement
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $member_id, $harambee_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch and return the group and Harambee details if the member is found
    if ($row = $result->fetch_assoc()) {
        return [
            'harambee_group_id' => $row['harambee_group_id'],
            'harambee_group_name' => $row['harambee_group_name'],
            'harambee_group_target' => $row['harambee_group_target'],
            'date_created' => $row['date_created'],  // Include date_created
            'harambee_description' => $row['harambee_description']
        ];
    } else {
        return false;
    }
}




// Retrive all members of a given harambee group
function getHarambeeGroupMemberIds($conn, $target, $harambee_group_id) {
    // Determine the correct table based on the target
    switch ($target) {
        case 'head-parish':
            $table = "head_parish_harambee_group_members";
            break;
        case 'sub-parish':
            $table = "sub_parish_harambee_group_members";
            break;
        case 'community':
            $table = "community_harambee_group_members";
            break;
        case 'group':
        case 'groups':
            $table = "groups_harambee_group_members";
            break;
        default:
            return false; // Invalid target
    }

    // Prepare SQL query to retrieve all member IDs for the specified Harambee group
    $query = "
        SELECT member_id 
        FROM $table 
        WHERE harambee_group_id = ?
    ";

    // Execute the query
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $harambee_group_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch all member IDs and store them in an array
    $member_ids = [];
    while ($row = $result->fetch_assoc()) {
        $member_ids[] = $row['member_id'];
    }

    // Close the statement and return the member IDs
    $stmt->close();
    return $member_ids;
}

function getHarambeeGroupContributions($conn, $harambee_id, $member_ids, $group_start_date, $target) {
    // Determine the correct contributions table based on the target
    $table = '';
    switch ($target) {
        case 'head-parish':
            $table = "head_parish_harambee_contribution";
            break;
        case 'sub-parish':
            $table = "sub_parish_harambee_contribution";
            break;
        case 'community':
            $table = "community_harambee_contribution";
            break;
        case 'group':
        case 'groups':
            $table = "groups_harambee_contribution";
            break;
        default:
            return ["success" => false, "message" => "Invalid target type"];
    }

    // If member_ids is empty, return an empty array instead of an error message
    if (empty($member_ids)) {
        return [];
    }

    // Prepare SQL query to retrieve contributions
    $member_ids_placeholder = implode(',', array_fill(0, count($member_ids), '?'));
    $query = "
        SELECT contribution_date, SUM(amount) AS total_contribution
        FROM $table
        WHERE harambee_id = ?
        AND member_id IN ($member_ids_placeholder)
        AND contribution_date >= ?
        GROUP BY contribution_date
        ORDER BY contribution_date ASC
    ";

    // Prepare the statement
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        return ["success" => false, "message" => "Failed to prepare statement: " . $conn->error];
    }

    // Bind parameters
    $types = str_repeat('i', count($member_ids)) . 'is'; // Bind 'i' for each member_id, and 'i' for harambee_id, 's' for group_start_date
    $params = array_merge([$harambee_id], $member_ids, [$group_start_date]);
    $stmt->bind_param($types, ...$params);

    // Execute and get results
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch contributions grouped by date
    $contributions = [];
    while ($row = $result->fetch_assoc()) {
        $contributions[] = $row;
    }

    $stmt->close();
    return $contributions;
}



function getHarambeeGroupMemberContribution($conn, $harambee_id, $member_id, $group_start_date, $target) {
    // Determine the correct contributions table based on the target
    $table = '';
    switch ($target) {
        case 'head-parish':
            $table = "head_parish_harambee_contribution";
            break;
        case 'sub-parish':
            $table = "sub_parish_harambee_contribution";
            break;
        case 'community':
            $table = "community_harambee_contribution";
            break;
        case 'group':
        case 'groups':
            $table = "groups_harambee_contribution";
            break;
        default:
            return 0;  // Invalid target, returning 0
    }

    // Prepare SQL query to retrieve total contributions for the given member
    $query = "
        SELECT SUM(amount) AS total_contribution
        FROM $table
        WHERE harambee_id = ? 
        AND member_id = ? 
        AND contribution_date >= ?
    ";

    // Prepare the statement
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        return 0;  // Error in preparing the statement, return 0
    }

    // Bind parameters
    $stmt->bind_param('iis', $harambee_id, $member_id, $group_start_date);

    // Execute the query
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch the total contribution
    $contribution = $result->fetch_assoc();

    $stmt->close();

    // Return the total contribution or 0 if no contribution is found
    return $contribution && isset($contribution['total_contribution']) ? $contribution['total_contribution'] : 0;
}

function getHarambeeGroupMemberTarget($conn, $harambee_id, $member_id, $target) {
    // Determine the correct contributions table based on the target
    $table = '';
    switch ($target) {
        case 'head-parish':
            $table = "head_parish_harambee_group_members";
            break;
        case 'sub-parish':
            $table = "sub_parish_harambee_group_members";
            break;
        case 'community':
            $table = "community_harambee_group_members";
            break;
        case 'group':
        case 'groups':
            $table = "groups_harambee_group_members";
            break;
        default:
            return 0;  // Invalid target, returning 0
    }

    // Prepare SQL query to retrieve total contributions for the given member
    $query = "
        SELECT target_amount
        FROM $table
        WHERE harambee_id = ? 
        AND member_id = ?
    ";

    // Prepare the statement
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        return 0;  // Error in preparing the statement, return 0
    }

    // Bind parameters
    $stmt->bind_param('ii', $harambee_id, $member_id);

    // Execute the query
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch the total contribution
    $target = $result->fetch_assoc();

    $stmt->close();

    // Return the total contribution or 0 if no contribution is found
    return $target && isset($target['target_amount']) ? $target['target_amount'] : 0;
}


function geTotalHarambeeGroupContribution($conn, $member_ids, $harambee_id, $target, $contribution_start_date) {
    // Define the contributions table based on the target
    switch ($target) {
        case 'head-parish':
            $table = "head_parish_harambee_contribution";
            break;
        case 'sub-parish':
            $table = "sub_parish_harambee_contribution";
            break;
        case 'community':
            $table = "community_harambee_contribution";
            break;
        case 'group':
        case 'groups':
            $table = "groups_harambee_contribution";
            break;
        default:
            return false; // Invalid target
    }

    // Prepare placeholders for the list of member IDs
    $placeholders = implode(',', array_map('intval', $member_ids)); 

    // Prepare the SQL query to get the total contributions
    $query = "
        SELECT SUM(amount) AS total_contributions
        FROM $table
        WHERE harambee_id = $harambee_id 
        AND member_id IN ($placeholders)
        AND contribution_date >= '$contribution_start_date' 
    ";

    // Execute the query
    $result = $conn->query($query);
    
    // Fetch the result and return the total contributions
    if ($result) {
        $row = $result->fetch_assoc();
        return $row ? $row['total_contributions'] : 0;
    } else {
        // Handle query error
        return 0;
    }
}


function getMemberDetails($conn, $member_id) {
    $base_avatar_url = "https://kanisalangu.sewmrtechnologies.com/uploads/avatars/";

    $member_details_query = "
        SELECT
            cm.member_id,
            cm.first_name, 
            cm.middle_name, 
            cm.last_name, 
            t.name AS title, 
            cm.phone,
            cm.type,
            cm.envelope_number,
            cm.email,
            CONCAT('DAYOSISI YA ', d.diocese_name) AS diocese_name, 
            CONCAT('JIMBO LA ', p.province_name) AS province_name, 
            CONCAT('USHARIKA WA ', hp.head_parish_name) AS head_parish_name,
            c.community_name,
            sp.sub_parish_name,
            sp.sub_parish_id,
            c.community_id,
            hp.head_parish_id,
            CASE
                WHEN cma.avatar_url IS NOT NULL THEN CONCAT('$base_avatar_url', cma.avatar_url)
                ELSE NULL
            END AS avatar_url
        FROM church_members cm
        LEFT JOIN titles t ON cm.title_id = t.id
        LEFT JOIN head_parishes hp ON cm.head_parish_id = hp.head_parish_id
        LEFT JOIN sub_parishes sp ON cm.sub_parish_id = sp.sub_parish_id
        LEFT JOIN communities c ON cm.community_id = c.community_id
        LEFT JOIN provinces p ON hp.province_id = p.province_id
        LEFT JOIN dioceses d ON p.diocese_id = d.diocese_id
        LEFT JOIN church_members_accounts cma ON cm.member_id = cma.member_id
        WHERE cm.member_id = ?
    ";

    $stmt = $conn->prepare($member_details_query);
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    return $stmt->get_result();
}

function getMemberIdsByLocation($conn, $head_parish_id, $sub_parish_id, $community_id, $gender = null) {
    $query = "
        SELECT cm.member_id
        FROM church_members cm
        WHERE cm.head_parish_id = ?
          AND cm.sub_parish_id = ?
          AND cm.community_id = ?
          AND cm.status = 'Active'
    ";

    // Add gender filter
    if ($gender === 'Male' || $gender === 'Female') {
        $query .= " AND cm.gender = ?";
    } elseif ($gender === '') {
        $query .= " AND (cm.gender IS NULL OR cm.gender = '')";
    }

    // Prepare and bind based on gender
    if ($gender === 'Male' || $gender === 'Female') {
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iiis", $head_parish_id, $sub_parish_id, $community_id, $gender);
    } else {
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iii", $head_parish_id, $sub_parish_id, $community_id);
    }

    $stmt->execute();
    $result = $stmt->get_result();
    $member_ids = [];

    while ($row = $result->fetch_assoc()) {
        $member_ids[] = $row['member_id'];
    }

    return $member_ids;
}



function getMemberDetailsByEnvelope($conn, $envelope_number) {
    // Query to fetch member details based on envelope number
    $member_details_query = "
        SELECT
            cm.member_id,
            cm.sub_parish_id,
            cm.community_id
        FROM church_members cm
        WHERE cm.envelope_number = ? OR cm.phone = ?
    ";
    
    // Prepare and execute the query
    $stmt = $conn->prepare($member_details_query);
    $stmt->bind_param("ss", $envelope_number, $envelope_number);  
    $stmt->execute();
    
    // Get the result
    $result = $stmt->get_result();
    
    // If a row is returned, fetch and return the data
    if ($result->num_rows > 0) {
        return $result->fetch_assoc();  
    }
    
    // If no member found, return null
    return null;
}

function recordEnvelopeData($conn, $member_id, $amount, $contribution_date, $payment_method, $head_parish_id, $sub_parish_id, $community_id, $local_timestamp) {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Prepare the SQL query to insert envelope contribution data
    $stmt = $conn->prepare("INSERT INTO envelope_contribution (member_id, amount, contribution_date, recorded_by, head_parish_id, sub_parish_id, community_id, payment_method, local_timestamp) 
                            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    // Bind the parameters to the prepared statement
    $stmt->bind_param("iisisssss", $member_id, $amount, $contribution_date, $_SESSION['head_parish_admin_id'], $head_parish_id, $sub_parish_id, $community_id, $payment_method, $local_timestamp);
    
    // Execute the statement
    if ($stmt->execute()) {
        $stmt->close();
        return true;  // Return true if the contribution was successfully recorded
    } else {
        $stmt->close();
        return false;  // Return false if there was an error while inserting
    }
}

function recordHarambeeTarget($conn, $harambee_id, $target_type, $target_table, $target, $head_parish_id, $sub_parish_id, $community_id, $member_id, $second_member_id = null, $group_name = null) {

    // Common table for individual targets
    $individualTables = [
        'head-parish' => 'head_parish_harambee_targets',
        'sub-parish' => 'sub_parish_harambee_targets',
        'community'   => 'community_harambee_targets',
        'groups'      => 'groups_harambee_targets'
    ];

    // Group info tables
    $groupInfoTables = [
        'head-parish' => 'hp_group_harambee_target_information',
        'sub-parish'  => 'sp_group_harambee_target_information',
        'community'   => 'com_group_harambee_target_information',
        'groups'      => 'gp_group_harambee_target_information'
    ];

    if (!isset($individualTables[$target_table])) {
        return false; // Invalid target_table
    }

    $individualTable = $individualTables[$target_table];
    $groupInfoTable = $groupInfoTables[$target_table];

    if ($target_type === 'individual') {
        // Check if the target exists for the individual
        $checkIndividual = $conn->prepare("SELECT target FROM $individualTable WHERE harambee_id = ? AND member_id = ?");
        $checkIndividual->bind_param("ii", $harambee_id, $member_id);
        $checkIndividual->execute();
        $checkIndividual->bind_result($existingTarget);
        $exists = $checkIndividual->fetch();
        $checkIndividual->close();

        if ($exists) {
            if ($target > $existingTarget) {
                // Update the individual target if the new target is greater
                $updateIndividual = $conn->prepare("UPDATE $individualTable SET target = ? WHERE harambee_id = ? AND member_id = ?");
                $updateIndividual->bind_param("dii", $target, $harambee_id, $member_id);
                $result = $updateIndividual->execute();
                $updateIndividual->close();
                return $result;
            }
            return true; // No update needed
        } else {
            // Insert new individual target
            $stmt = $conn->prepare("INSERT INTO $individualTable (harambee_id, member_id, target, target_type, head_parish_id, sub_parish_id, community_id)
                                    VALUES (?, ?, ?, 'individual', ?, ?, ?)");
            $stmt->bind_param("iidiii", $harambee_id, $member_id, $target, $head_parish_id, $sub_parish_id, $community_id);
            $result = $stmt->execute();
            $stmt->close();
            return $result;
        }

    } elseif ($target_type === 'group' && $second_member_id !== null && $group_name !== null) {
        // Check if group exists
        $checkGroup = $conn->prepare("SELECT target FROM $groupInfoTable WHERE harambee_id = ? AND first_member_id = ? AND second_member_id = ?");
        $checkGroup->bind_param("iii", $harambee_id, $member_id, $second_member_id);
        $checkGroup->execute();
        $checkGroup->bind_result($existingGroupTarget);
        $exists = $checkGroup->fetch();
        $checkGroup->close();

        if ($exists) {
            if ($target > $existingGroupTarget) {
                // Update group info table
                $updateGroup = $conn->prepare("UPDATE $groupInfoTable SET target = ?, group_name = ? WHERE harambee_id = ? AND first_member_id = ? AND second_member_id = ?");
                $updateGroup->bind_param("dsiii", $target, $group_name, $harambee_id, $member_id, $second_member_id);
                $groupResult = $updateGroup->execute();
                $updateGroup->close();

                // Update both individual entries
                $updateInd1 = $conn->prepare("UPDATE $individualTable SET target = ? WHERE harambee_id = ? AND member_id = ? AND target_type = 'group'");
                $updateInd1->bind_param("dii", $target, $harambee_id, $member_id);
                $res1 = $updateInd1->execute();
                $updateInd1->close();

                $updateInd2 = $conn->prepare("UPDATE $individualTable SET target = ? WHERE harambee_id = ? AND member_id = ? AND target_type = 'group'");
                $updateInd2->bind_param("dii", $target, $harambee_id, $second_member_id);
                $res2 = $updateInd2->execute();
                $updateInd2->close();

                return $groupResult && $res1 && $res2;
            }
            return true; // No update needed
        } else {
            // Insert both individual targets
            $stmt1 = $conn->prepare("INSERT INTO $individualTable (harambee_id, member_id, target, target_type, head_parish_id, sub_parish_id, community_id)
                                     VALUES (?, ?, ?, 'group', ?, ?, ?)");
            $stmt1->bind_param("iidiii", $harambee_id, $member_id, $target, $head_parish_id, $sub_parish_id, $community_id);
            $result1 = $stmt1->execute();
            $stmt1->close();

            $stmt2 = $conn->prepare("INSERT INTO $individualTable (harambee_id, member_id, target, target_type, head_parish_id, sub_parish_id, community_id)
                                     VALUES (?, ?, ?, 'group', ?, ?, ?)");
            $stmt2->bind_param("iidiii", $harambee_id, $second_member_id, $target, $head_parish_id, $sub_parish_id, $community_id);
            $result2 = $stmt2->execute();
            $stmt2->close();

            // Insert into the group information table
            $stmt3 = $conn->prepare("INSERT INTO $groupInfoTable (harambee_id, first_member_id, second_member_id, target, group_name)
                                     VALUES (?, ?, ?, ?, ?)");
            $stmt3->bind_param("iiids", $harambee_id, $member_id, $second_member_id, $target, $group_name);
            $result3 = $stmt3->execute();
            $stmt3->close();

            return $result1 && $result2 && $result3;
        }
    }

    return false; // In case of invalid target_type or missing data
}


function getHarambeeDescription($conn, $harambee_id, $harambee_table) {
    $harambee_desc_query = "SELECT description FROM $harambee_table WHERE harambee_id = ?";
    $stmt = $conn->prepare($harambee_desc_query);
    $stmt->bind_param("i", $harambee_id);
    $stmt->execute();
    return $stmt->get_result();
}

function calculateTotalContributions($conn, $member_id, $harambee_id, $contribution_table) {
    $contribution_query = "SELECT SUM(amount) AS total_contribution FROM $contribution_table WHERE member_id = ? AND harambee_id = ?";
    $stmt = $conn->prepare($contribution_query);
    $stmt->bind_param("ii", $member_id, $harambee_id);
    $stmt->execute();
    return $stmt->get_result();
}

function getContributionsByDate($conn, $member_id, $harambee_id, $contribution_table) {
    $contribution_query = "
        SELECT contribution_date, SUM(amount) AS amount_contributed, payment_method 
        FROM $contribution_table 
        WHERE member_id = ? AND harambee_id = ? 
        GROUP BY contribution_date, payment_method
        ORDER BY contribution_date ASC";
    
    $stmt = $conn->prepare($contribution_query);
    $stmt->bind_param("ii", $member_id, $harambee_id);
    $stmt->execute();
    return $stmt->get_result();
}


function getTotalContributionsOnDate($conn, $harambee_id, $member_id, $date, $target_table) {
    // Prepare the SQL query to get the total contributions before and on the given date grouped by payment method
    $query = "
        SELECT 
            payment_method,
            SUM(CASE WHEN contribution_date < ? THEN amount ELSE 0 END) AS before_date,
            SUM(CASE WHEN contribution_date = ? THEN amount ELSE 0 END) AS on_date
        FROM $target_table
        WHERE harambee_id = ? AND member_id = ?
        GROUP BY payment_method
    ";
    
    // Prepare the statement
    $stmt = $conn->prepare($query);
    
    // Bind parameters
    $stmt->bind_param('ssii', $date, $date, $harambee_id, $member_id);
    
    // Execute the query
    $stmt->execute();
    
    // Get the result
    $result = $stmt->get_result();
    
    // Initialize variables to store the summed before_date and on_date contributions
    $total_before_date = 0;
    $on_date_contributions = [];
    $total_on_date_contributions = 0;
    // Fetch the data
    while ($row = $result->fetch_assoc()) {
        // Accumulate the total before the date
        $total_before_date += $row['before_date'] ? $row['before_date'] : 0;
        
        // Store the on_date contributions by payment method
        $on_date_contributions[$row['payment_method']] = $row['on_date'] ? $row['on_date'] : 0;
        $total_on_date_contributions += $row['on_date'] ? $row['on_date'] : 0;
    }
    
    // Return the total sum of contributions before the date and the on_date contributions
    return [
        'total_before_date' => $total_before_date,
        'on_date_contributions' => $on_date_contributions,
        'contribution' => $total_on_date_contributions
    ];
}

function getTotalContributionsBetweenDates($conn, $harambee_id, $member_id, $from_date, $to_date = null, $target_table, $recorded_by = null) {
    // Prepare the SQL query to get the total contributions and latest local_timestamp
    $query = "
        SELECT 
            SUM(CASE WHEN contribution_date < ? THEN amount ELSE 0 END) AS before_date,
            SUM(CASE WHEN contribution_date >= ? AND (? IS NULL OR contribution_date <= ?) THEN amount ELSE 0 END) AS on_date,
            MAX(CASE WHEN contribution_date >= ? AND (? IS NULL OR contribution_date <= ?) THEN local_timestamp ELSE NULL END) AS latest_local_timestamp,
            GROUP_CONCAT(DISTINCT CASE WHEN contribution_date >= ? AND (? IS NULL OR contribution_date <= ?) THEN payment_method ELSE NULL END) AS payment_methods_on_date
        FROM $target_table
        WHERE harambee_id = ? AND member_id = ?

    ";
    
    // Add recorded_by condition if it's not null
    if ($recorded_by !== null) {
        $query .= " AND recorded_by = ?";
    }

    // Prepare the statement
    $stmt = $conn->prepare($query);

    // Bind parameters
    if ($recorded_by !== null) {
        if ($to_date !== null) {
            $stmt->bind_param(
                'ssssssssssiii',
                $from_date, $from_date, $to_date, $to_date,
                $from_date, $to_date, $to_date,
                $from_date, $to_date, $to_date,
                $harambee_id, $member_id, $recorded_by
            );
        } else {
            $stmt->bind_param(
                'ssssssssssiii',
                $from_date, $from_date, $from_date, $from_date,
                $from_date, $from_date, $from_date,
                $from_date, $to_date, $to_date,
                $harambee_id, $member_id, $recorded_by
            );
        }
    } else {
        if ($to_date !== null) {
            $stmt->bind_param(
                'ssssssssssii',
                $from_date, $from_date, $to_date, $to_date,
                $from_date, $to_date, $to_date,
                $from_date, $to_date, $to_date,
                $harambee_id, $member_id
            );
        } else {
            $stmt->bind_param(
                'ssssssssssii',
                $from_date, $from_date, $from_date, $from_date,
                $from_date, $from_date, $from_date,
                $from_date, $to_date, $to_date,
                $harambee_id, $member_id
            );
        }
    }

    // Execute and fetch
    $stmt->execute();
    $result = $stmt->get_result();

    $total_before_date = 0;
    $total_on_date = 0;
    $latest_local_timestamp = null;

    if ($row = $result->fetch_assoc()) {
        $total_before_date = $row['before_date'] ?: 0;
        $total_on_date = $row['on_date'] ?: 0;
        $latest_local_timestamp = $row['latest_local_timestamp'];
        $payment_methods = $row['payment_methods_on_date'] ? explode(',', $row['payment_methods_on_date']) : [];
    }

    return [
        'total_before_date' => $total_before_date,
        'on_date_contributions' => $total_on_date,
        'total_contributed' => $total_before_date + $total_on_date,
        'latest_local_timestamp' => $latest_local_timestamp,
        'payment_methods' => $payment_methods
    ];
}



function getGroupDetails($conn, $harambee_id, $group_info_table, $member_id) {
    $group_query = "SELECT first_member_id, second_member_id, group_name 
                    FROM $group_info_table 
                    WHERE harambee_id = ? 
                    AND (first_member_id = ? OR second_member_id = ?)";

    $stmt = $conn->prepare($group_query);
    $stmt->bind_param("iii", $harambee_id, $member_id, $member_id); // Bind harambee_id and member_id to the query
    $stmt->execute();
    return $stmt->get_result();
}


function getTablesByTarget($target) {
    switch ($target) {
        case 'head-parish':
            return ['head_parish_harambee_targets', 'hp_group_harambee_target_information', 'head_parish_harambee', 'head_parish_harambee_contribution','head_parish_harambee_group_members'];
        case 'sub-parish':
            return ['sub_parish_harambee_targets', 'sp_group_harambee_target_information', 'sub_parish_harambee', 'sub_parish_harambee_contribution','sub_parish_harambee_group_members'];
        case 'community':
            return ['community_harambee_targets', 'com_group_harambee_target_information', 'community_harambee', 'community_harambee_contribution','community_harambee_group_members'];
        case 'groups':
            return ['groups_harambee_targets', 'gp_group_harambee_target_information', 'groups_harambee', 'groups_harambee_contribution','groups_harambee_group_members'];
        default:
            return false;
    }
}



function getHarambeeIdFromHarambeeGroup($conn, $harambee_group_id, $target) {
    // Define the table based on the target type
    $table = '';
    switch ($target) {
        case 'head-parish':
            $table = 'head_parish_harambee_groups';
            break;
        case 'sub-parish':
            $table = 'sub_parish_harambee_groups';
            break;
        case 'community':
            $table = 'community_harambee_groups';
            break;
        case 'group':
        case 'groups':
            $table = 'groups_harambee_groups';
            break;
        default:
            return null; // Invalid target type returns null
    }

    // Prepare the SQL statement
    $query = "SELECT harambee_id FROM $table WHERE harambee_group_id = ?";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        return null; // Return null if the statement fails to prepare
    }

    // Bind parameters and execute the query
    $stmt->bind_param("i", $harambee_group_id);
    $stmt->execute();
    $stmt->bind_result($harambee_id);
    $stmt->fetch();
    $stmt->close();

    // Return the harambee_id or null if not found
    return $harambee_id !== null ? $harambee_id : null;
}



function hasGroupTargetType($conn, $harambeeId, $memberId, $target) {
    $tables = getTablesByTarget($target);

    if ($tables === false || count($tables) < 1) {
        return false; // Return false if no valid tables are found
    }

    $targetTable = $tables[0]; 

    $stmt = $conn->prepare("SELECT target_type FROM $targetTable WHERE harambee_id = ? AND member_id = ?");
    if (!$stmt) {
        die("Preparation failed: " . $conn->error);
    }

    $stmt->bind_param("ii", $harambeeId, $memberId);
    $stmt->execute();
    $stmt->bind_result($targetType);
    $stmt->fetch();
    $stmt->close();

    return ($targetType === 'group');
}

function isInHarambeeGroup($conn, $harambeeId, $memberId, $target) {
    // Get the relevant tables based on the target
    $tables = getTablesByTarget($target);

    // If no tables were found or the result is invalid, return false
    if ($tables === false || count($tables) < 1) {
        return false;
    }

    // Assume the groups table is at index 4 of the tables array
    $groupsTable = $tables[4];

    // Prepare the SQL query to check membership
    $stmt = $conn->prepare("SELECT member_id FROM $groupsTable WHERE harambee_id = ? AND member_id = ?");
    if (!$stmt) {
        die("SQL preparation failed: " . $conn->error);
    }

    // Bind parameters and execute the query
    $stmt->bind_param("ii", $harambeeId, $memberId);
    $stmt->execute();

    // Bind the result to a variable and fetch the result
    $stmt->bind_result($resultMemberId);
    $found = $stmt->fetch();

    // Close the statement
    $stmt->close();

    // Return true if a match was found, false otherwise
    return $found ? true : false;
}


function getMrAndMrsMembersIds($conn, $harambeeId, $memberId, $target) {
    // Get the relevant tables based on the target
    $tables = getTablesByTarget($target);

    if ($tables === false || count($tables) < 2) {
        return false; // Return false if no valid tables are found
    }

    $informationTable = $tables[1]; 

    // Prepare the query
    $stmt = $conn->prepare("SELECT first_member_id, second_member_id, group_name FROM $informationTable WHERE harambee_id = ? AND (first_member_id = ? OR second_member_id = ?)");
    if (!$stmt) {
        die("Preparation failed: " . $conn->error);
    }

    // Bind the parameters
    $stmt->bind_param("iii", $harambeeId, $memberId, $memberId);
    $stmt->execute();

    // Fetch the results
    $stmt->bind_result($firstMemberId, $secondMemberId, $groupName);

    $members = [];
    while ($stmt->fetch()) {
        $members[] = [
            'first_member_id' => $firstMemberId,
            'second_member_id' => $secondMemberId,
            'group_name' => $groupName
        ];
    }

    $stmt->close();

    // Return the members as an array of pairs
    return $members;
}

function getMrAndMrsMembersIdsFromGroupId($conn, $harambeeId, $sharedGroupId, $target) {
    // Get the relevant tables based on the target
    $tables = getTablesByTarget($target);

    if ($tables === false || count($tables) < 2) {
        return false; // Return false if no valid tables are found
    }

    $informationTable = $tables[1]; 

    // Prepare the query
    $stmt = $conn->prepare("SELECT first_member_id, second_member_id, group_name FROM $informationTable WHERE id = ? AND harambee_id = ?");
    if (!$stmt) {
        die("Preparation failed: " . $conn->error);
    }

    // Bind the parameters
    $stmt->bind_param("ii", $sharedGroupId, $harambeeId);
    $stmt->execute();

    // Fetch the results
    $stmt->bind_result($firstMemberId, $secondMemberId, $groupName);

    $members = [];
    while ($stmt->fetch()) {
        $members[] = [
            'first_member_id' => $firstMemberId,
            'second_member_id' => $secondMemberId,
            'group_name' => $groupName
        ];
    }

    $stmt->close();

    // Return the members as an array of pairs
    return $members;
}

// New function to get member's target and total contributions
function getMemberTargetAndContributions($conn, $harambee_id, $member_id, $target) {
    // Define tables based on target
    $tables = getTablesByTarget($target);
    if (!$tables) return false;

    list($target_table, $group_info_table, $harambee_table, $contribution_table) = $tables;

    // Step 1: Get the member's target (or default to 0 if no target found)
    $target_result = getTargetDetails($conn, $harambee_id, $member_id, $target_table);
    $target_amount = $target_result->num_rows > 0 ? $target_result->fetch_assoc()['target_amount'] : 0;

    // Step 2: Get the member's total contributions
    $contribution_result = calculateTotalContributions($conn, $member_id, $harambee_id, $contribution_table);
    $total_contribution = $contribution_result->fetch_assoc()['total_contribution'] ?? 0;

    return [
        'target_amount' => $target_amount,
        'total_contribution' => $total_contribution
    ];
}


function getHarambeeMemberIds($conn, $harambee_id, $target, $community_id = null) {
    // Get the relevant tables based on the target
    $tables = getTablesByTarget($target);
    if (!$tables) return false;

    list($target_table, $group_info_table, $harambee_table, $contribution_table) = $tables;

    // Step 1: Get member IDs from the target table
    $target_query = "SELECT member_id FROM $target_table WHERE harambee_id = ?";
    
    if ($community_id !== null) {
        $target_query .= " AND community_id = ?";
    }

    $stmt = $conn->prepare($target_query);

    if ($community_id !== null) {
        $stmt->bind_param("ii", $harambee_id, $community_id);
    } else {
        $stmt->bind_param("i", $harambee_id);
    }

    $stmt->execute();
    $target_result = $stmt->get_result();

    $target_member_ids = [];
    while ($row = $target_result->fetch_assoc()) {
        $target_member_ids[] = $row['member_id'];
    }

    $contribution_member_ids = [];

    // Step 2: Only execute if there are target member IDs
    if (count($target_member_ids) > 0) {
        $target_ids_placeholder = implode(',', array_fill(0, count($target_member_ids), '?'));
        $contribution_query = "SELECT member_id FROM $contribution_table WHERE harambee_id = ? 
                               AND member_id NOT IN ($target_ids_placeholder)";
        
        if ($community_id !== null) {
            $contribution_query .= " AND community_id = ?";
        }

        $stmt = $conn->prepare($contribution_query);

        $types = str_repeat('i', count($target_member_ids) + 1);
        $params = array_merge([$harambee_id], $target_member_ids);
        if ($community_id !== null) {
            $types .= 'i';
            $params[] = $community_id;
        }

        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $contribution_result = $stmt->get_result();

        while ($row = $contribution_result->fetch_assoc()) {
            $contribution_member_ids[] = $row['member_id'];
        }
    }

    // Step 3: Merge both sets of member IDs and return
    $all_member_ids = array_merge($target_member_ids, $contribution_member_ids);

    return array_unique($all_member_ids);
}




function getContributingHarambeeMemberIds($conn, $harambee_id, $target) {
    // Get the relevant tables based on the target
    $tables = getTablesByTarget($target);  // We need the target parameter for table retrieval
    if (!$tables) return false;

    list($target_table, $group_info_table, $harambee_table, $contribution_table) = $tables;

    // Step 1: Create the query for fetching member IDs based on harambee_id, without date filter
    $contribution_query = "SELECT DISTINCT member_id FROM $contribution_table 
                           WHERE harambee_id = ?";
    $stmt = $conn->prepare($contribution_query);
    // Bind harambee_id
    $stmt->bind_param("i", $harambee_id);  // 'i' for integer (harambee_id)

    // Execute the query
    $stmt->execute();
    $contribution_result = $stmt->get_result();

    // Step 2: Fetch the member IDs from the result
    $member_ids = [];
    while ($row = $contribution_result->fetch_assoc()) {
        $member_ids[] = $row['member_id'];
    }

    // Return the unique member IDs
    return $member_ids;  
}

function getTotalContributionBySubParishFromArray($member_details_array, $sub_parish_id) {
    // Initialize a variable to store the total contribution
    $total_contribution = 0;

    // Iterate through the member details array
    foreach ($member_details_array as $member) {
        // Check if the member's sub_parish_id matches the provided sub_parish_id
        if ($member['sub_parish_id'] == $sub_parish_id) {
            // Add the member's total contribution to the total
            $total_contribution += $member['contribution'];
        }
    }

    // Return the total contribution for the specified sub_parish_id
    return $total_contribution;
}

function getTotalContributionFromArray($member_details_array) {
    // Initialize a variable to store the total contribution
    $total_contribution = 0;

    // Iterate through the member details array
    foreach ($member_details_array as $member) {

        // Add the member's total contribution to the total
        $total_contribution += $member['contribution'];

    }

    // Return the total contribution for the specified sub_parish_id
    return $total_contribution;
}


function getHarambeeMemberIdsByContributionDate($conn, $harambee_id, $target, $from_date = null, $to_date = null) {
    $tables = getTablesByTarget($target);
    if (!$tables) return false;

    list($target_table, $group_info_table, $harambee_table, $contribution_table) = $tables;

    if ($from_date !== null && $to_date !== null) {
        $query = "SELECT DISTINCT member_id FROM $contribution_table 
                  WHERE harambee_id = ? AND contribution_date BETWEEN ? AND ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("iss", $harambee_id, $from_date, $to_date);
    } elseif ($from_date !== null) {
        $query = "SELECT DISTINCT member_id FROM $contribution_table 
                  WHERE harambee_id = ? AND contribution_date = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("is", $harambee_id, $from_date);
    } else {
        $query = "SELECT DISTINCT member_id FROM $contribution_table 
                  WHERE harambee_id = ?";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $harambee_id);
    }

    $stmt->execute();
    $result = $stmt->get_result();

    $member_ids = [];
    while ($row = $result->fetch_assoc()) {
        $member_ids[] = $row['member_id'];
    }

    return $member_ids;
}




function getContributionMethods($conn, $member_id, $harambee_id, $target, $contribution_date = null) {
    // Get the relevant tables based on the target
    $tables = getTablesByTarget($target);  // We need the target parameter for table retrieval
    if (!$tables) return false;

    list($target_table, $group_info_table, $harambee_table, $contribution_table) = $tables;
    
    // Base query to fetch the total amount contributed by payment method
    $contribution_query = "
        SELECT payment_method, SUM(amount) AS total_amount
        FROM $contribution_table
        WHERE member_id = ? AND harambee_id = ?";

    // Add date filter if a contribution_date is provided
    if ($contribution_date) {
        $contribution_query .= " AND contribution_date = ?";
    }

    // Group the results by payment method
    $contribution_query .= " GROUP BY payment_method
                             ORDER BY payment_method ASC";

    // Prepare the statement
    $stmt = $conn->prepare($contribution_query);

    // Bind parameters dynamically based on whether a contribution_date is provided
    if ($contribution_date) {
        $stmt->bind_param("iss", $member_id, $harambee_id, $contribution_date); // 's' for string (date)
    } else {
        $stmt->bind_param("ii", $member_id, $harambee_id); // No date filter
    }

    // Execute the statement
    $stmt->execute();

    // Get the result set
    $result = $stmt->get_result();

    // Initialize an array to store the contributions by payment method
    $methods = [];
    while ($row = $result->fetch_assoc()) {
        $methods[$row['payment_method']] = (float)$row['total_amount'];
    }

    // Return the array of contributions by payment method
    return $methods;
}



function handleFallback($conn, $member_id, $harambee_id, $harambee_table, $contribution_table, $target) {
    // Get sub_parish_id and community_id from church_members table
    $sub_parish_and_community = getSubParishAndCommunity($member_id, $conn);
    if ($sub_parish_and_community) {
        $sub_parish_id = $sub_parish_and_community['sub_parish_id'];
        $community_id = $sub_parish_and_community['community_id'];

        // Get member details
        $member_data = getMemberDetails($conn, $member_id)->fetch_assoc();
        $harambee_description = getHarambeeDescription($conn, $harambee_id, $harambee_table)->fetch_assoc()['description'] ?? '';
        $total_contribution = calculateTotalContributions($conn, $member_id, $harambee_id, $contribution_table)->fetch_assoc()['total_contribution'] ?? 0;
        $isInGroups = isInHarambeeGroup($conn, $harambee_id, $member_id, $target);
        // Return member data with null target_amount
        return [
            'member_id' => $member_id,
            'sub_parish_id' => $sub_parish_id,
            'community_id' => $community_id,
            'first_name' => $member_data['first_name'],
            'middle_name' => $member_data['middle_name'],
            'last_name' => $member_data['last_name'],
            'envelope_number' => $member_data['envelope_number'],
            'title' => $member_data['title'],
            'member_type' => $member_data['type'],
            'phone' => $member_data['phone'],
            'email' => $member_data['email'],
            'diocese_name' => $member_data['diocese_name'],
            'province_name' => $member_data['province_name'],
            'head_parish_name' => $member_data['head_parish_name'],
            'head_parish_id' => $member_data['head_parish_id'],
            'sub_parish_name' => $member_data['sub_parish_name'],
            'community_name' => $member_data['community_name'],
            'harambee_description' => $harambee_description,
            'target_amount' => null,
            'total_contribution' => $total_contribution,
            'responsibility' => null,
            'group_name' => null,
            'is_in_groups' => $isInGroups
        ];
    }

    return null;
}


function getHarambeeGroupInfo($conn, $target, $harambee_group_id) {
    // Prepare SQL query based on the target
    $query = "";
    switch ($target) {
        case 'head-parish':
            $query = "
                SELECT 
                    h.harambee_group_name, 
                    h.harambee_group_target, 
                    h.description,
                    DATE(h.date_created) AS date_created
                FROM 
                    head_parish_harambee_groups h
                WHERE 
                    h.harambee_group_id = ?";
            break;

        case 'sub-parish':
            $query = "
                SELECT 
                    h.harambee_group_name, 
                    h.harambee_group_target, 
                    h.description,
                    DATE(h.date_created) AS date_created
                FROM 
                    sub_parish_harambee_groups h
                WHERE 
                    h.harambee_group_id = ?";
            break;

        case 'community':
            $query = "
                SELECT 
                    h.harambee_group_name, 
                    h.harambee_group_target, 
                    h.description,
                    DATE(h.date_created) AS date_created
                FROM 
                    community_harambee_groups h
                WHERE 
                    h.harambee_group_id = ?";
            break;

        case 'group':
        case 'groups':
            $query = "
                SELECT 
                    h.harambee_group_name, 
                    h.harambee_group_target, 
                    h.description,
                    DATE(h.date_created) AS date_created
                FROM 
                    groups_harambee_groups h
                WHERE 
                    h.harambee_group_id = ?";
            break;
    }

    // Execute the query to fetch group details
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $harambee_group_id);
        $stmt->execute();
        $result = $stmt->get_result();

        // Fetch and return results
        if ($row = $result->fetch_assoc()) {
            return [
                "group_name" => $row['harambee_group_name'],
                "harambee_group_target" => number_format($row['harambee_group_target'], 0),
                "harambee_group_description" => $row['description'],
                "date_created" => $row['date_created'] 
            ];
        }
    }

    return false; 
}


function getSubParishAndCommunityNames($conn, $community_id) {
    // Define the SQL query with a placeholder for the community_id
    $sql = "SELECT c.community_name, sp.sub_parish_name 
            FROM communities c 
            JOIN sub_parishes sp ON sp.sub_parish_id = c.sub_parish_id 
            WHERE c.community_id = ?";

    // Prepare the statement
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        // Handle preparation error
        die("Preparation failed: " . $conn->error);
    }

    // Bind the community_id parameter
    $stmt->bind_param("i", $community_id);

    // Execute the statement
    $stmt->execute();

    // Get the result
    $result = $stmt->get_result();

    // Fetch the result as an associative array
    $data = $result->fetch_assoc();

    // Close the statement
    $stmt->close();

    // Return the result
    return $data;
}


function getMemberHarambeeDetails($conn, $member_id, $harambee_id, $target) {
    $response = ['members' => []];

    // Define tables based on target
    $tables = getTablesByTarget($target);
    if (!$tables) return false;

    list($target_table, $group_info_table, $harambee_table, $contribution_table) = $tables;

    // Step 1: Get target details
    $target_result = getTargetDetails($conn, $harambee_id, $member_id, $target_table);

    if ($target_result->num_rows > 0) {
        $row = $target_result->fetch_assoc();
        $sub_parish_id = $row['sub_parish_id'];
        $sub_parish_name = $row['sub_parish_name'];  
        $community_id = $row['community_id'];
        $community_name = $row['community_name'];    
        $target_amount = $row['target_amount'] ?? null;
        $target_type = $row['target_type'];         
        $harambee_committee_responsibility = $row['harambee_committee_responsibility'];
        

        if ($row['target_type'] == 'individual') {
            // Fetch individual member's details
            $member_data = getMemberDetails($conn, $member_id)->fetch_assoc();
            $harambee_description = getHarambeeDescription($conn, $harambee_id, $harambee_table)->fetch_assoc()['description'] ?? '';
            $total_contribution = calculateTotalContributions($conn, $member_id, $harambee_id, $contribution_table)->fetch_assoc()['total_contribution'] ?? 0;
            $isInGroups = isInHarambeeGroup($conn, $harambee_id, $member_id, $target);
            $response['members'][] = [
                'member_id' => $member_id,
                'sub_parish_id' => $sub_parish_id,
                'community_id' => $community_id,
                'first_name' => $member_data['first_name'],
                'middle_name' => $member_data['middle_name'],
                'last_name' => $member_data['last_name'],
                'envelope_number' => $member_data['envelope_number'],
                'title' => $member_data['title'],
                'member_type' => $member_data['type'],
                'phone' => $member_data['phone'],
                'email' => $member_data['email'],
                'diocese_name' => $member_data['diocese_name'],
                'province_name' => $member_data['province_name'],
                'head_parish_name' => $member_data['head_parish_name'],
                'head_parish_id' => $member_data['head_parish_id'],
                'sub_parish_name' => $sub_parish_name,
                'community_name' =>  $community_name,
                'harambee_description' => $harambee_description,
                'target_amount' => $target_amount, 
                'total_contribution' => $total_contribution,
                'responsibility' => $harambee_committee_responsibility,
                'group_name' => null,
                'is_in_groups' => $isInGroups
            ];
        } else {
            // Fetch group details
            $group_result = getGroupDetails($conn, $harambee_id, $group_info_table, $member_id);
            $isInGroups = false;
            while ($group_row = $group_result->fetch_assoc()) {
                foreach ([$group_row['first_member_id'], $group_row['second_member_id']] as $group_member_id) {
                    $member_data = getMemberDetails($conn, $group_member_id)->fetch_assoc();
                    $harambee_description = getHarambeeDescription($conn, $harambee_id, $harambee_table)->fetch_assoc()['description'] ?? '';
                    $total_contribution = calculateTotalContributions($conn, $group_member_id, $harambee_id, $contribution_table)->fetch_assoc()['total_contribution'] ?? 0;
                    $isInGroupsForMember  = isInHarambeeGroup($conn, $harambee_id, $group_member_id, $target);
                    if ($isInGroupsForMember) {
                        $isInGroups = true; // Set the flag to true if any member is in a group
                    }
                    $row = getTargetDetails($conn, $harambee_id, $group_member_id, $target_table)->fetch_assoc();
                    $sub_parish_id = $row['sub_parish_id'];
                    $sub_parish_name = $row['sub_parish_name'];  
                    $community_id = $row['community_id'];
                    $community_name = $row['community_name'];    
                    $target_amount = $row['target_amount'] ?? null;
                    $target_type = $row['target_type'];         
                    $harambee_committee_responsibility = $row['harambee_committee_responsibility'];

                    $response['members'][] = [
                        'member_id' => $group_member_id,
                        'sub_parish_id' => $sub_parish_id,
                        'community_id' => $community_id,
                        'first_name' => $member_data['first_name'],
                        'middle_name' => $member_data['middle_name'],
                        'last_name' => $member_data['last_name'],
                        'title' => $member_data['title'],
                        'member_type' => $member_data['type'],
                        'envelope_number' => $member_data['envelope_number'],
                        'phone' => $member_data['phone'],
                        'email' => $member_data['email'],
                        'diocese_name' => $member_data['diocese_name'],
                        'province_name' => $member_data['province_name'],
                        'head_parish_name' => $member_data['head_parish_name'],
                        'head_parish_id' => $member_data['head_parish_id'],
                        'sub_parish_name' => $sub_parish_name,
                        'community_name' =>  $community_name,
                        'harambee_description' => $harambee_description,
                        'target_amount' => $target_amount,
                        'total_contribution' => $total_contribution,
                        'responsibility' => $harambee_committee_responsibility,
                        'group_name' => $group_row['group_name'],
                        'is_in_groups' => $isInGroups 
                    ];
                }
            }
        }
    } else {
        // Fallback to the church_members table if no target details found
        $fallback_data = handleFallback($conn, $member_id, $harambee_id, $harambee_table, $contribution_table, $target);
        if ($fallback_data) {
            $response['members'][] = $fallback_data;
        }
    }

    return $response;
}

function getMemberFullName($member) {
    // Initialize an empty full name string
    $full_name = '';

    // Check if the title exists and is not empty, then add it
    if (!empty($member['title'])) {
        $full_name .= $member['title'] . ' ';
    }

    // Add the first name, middle name (if available), and last name
    $full_name .= strtoupper(trim($member['first_name'])) . ' ';

    if (!empty($member['middle_name'])) {
        $full_name .= strtoupper(trim($member['middle_name'])) . ' ';
    }

    $full_name .= strtoupper(trim($member['last_name']));

    // Return the constructed full name
    return trim($full_name); // Trim any excess spaces
}

function getParishInfo($conn, $head_parish_id) {
    // Prepare the SQL statement
    $sql = "
        SELECT 
            CONCAT('DAYOSISI YA ', d.diocese_name) AS diocese_name, 
            CONCAT('JIMBO LA ', p.province_name) AS province_name, 
            CONCAT('USHARIKA WA ', hp.head_parish_name) AS head_parish_name
        FROM head_parishes hp
        LEFT JOIN provinces p ON hp.province_id = p.province_id
        LEFT JOIN dioceses d ON p.diocese_id = d.diocese_id
        WHERE hp.head_parish_id = ?"; // Use the correct identifier for the head_parish_id

    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $head_parish_id); // Assuming head_parish_id is an integer
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch the result
    if ($row = $result->fetch_assoc()) {
        return [
            'diocese_name' => $row['diocese_name'],
            'province_name' => $row['province_name'],
            'head_parish_name' => $row['head_parish_name'],
        ];
    } else {
        return null; 
    }
}


function getSingleMemberHarambeeDetails($conn, $member_id, $harambee_id, $target) {
    // Call the existing function to get all member details
    $allMembersDetails = getMemberHarambeeDetails($conn, $member_id, $harambee_id, $target);

    // Check if the response contains members
    if (isset($allMembersDetails['members']) && !empty($allMembersDetails['members'])) {
        // Loop through members and return the data for the specified member
        foreach ($allMembersDetails['members'] as $member) {
            if ($member['member_id'] == $member_id) {
                return $member;
            }
        }
    }
    
    // If the member is not found, return false or a custom response
    return false;
}


function calculatePercentage($numerator, $denominator) {
    if ($denominator == 0) {
        return 0; 
    }
    $percentage = ($numerator / $denominator) * 100;
    return $percentage;
}

function numberToWords($number) {
    $f = new NumberFormatter("en", NumberFormatter::SPELLOUT);
    return ucfirst($f->format($number));
}


function sendExpenseRequestNotification($conn, $target, $role, $message, $subject = "Expense Request Notification", $title = "New Expense Request",$adminId) {
    // Define default email settings
    $from_email = "info@kanisalangu.sewmrtechnologies.com";
    $sender_name = "Kanisa Langu";
    $headers = "From: $sender_name <$from_email>\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    // Query database to get admin's email based on target and role
    $admin_email = getAdminEmailByTargetAndRole($conn, $target, $role, $adminId);

    if (!$admin_email) {
        // If no admin found for the specified role and target, return an error
        return false;
    }
    
    $prefix = getAdminPrefix($target);
    // Logo URL
    $logoUrl = 'https://kanisalangu.sewmrtechnologies.com/logo.png'; 

    // Construct the full HTML message
    $email_message = '
    <html>
    <head>
        <title>Expense Request Notification</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 0;
                padding: 0;
                background-color: #f4f4f4;
                color: #333;
            }
            .container {
                max-width: 1000px;
                margin: 20px auto;
                padding: 20px;
                background: #ffffff;
                border-radius: 8px;
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }
            .logo {
                display: block;
                margin: 0 auto 20px;
                max-width: 150px;
            }
            h1 {
                color: #6c757d;
                font-size: 24px;
                text-align: center;
                margin: 20px 0;
            }
            .content {
                font-size: 16px;
                line-height: 1.6;
            }
            .footer {
                text-align: center;
                margin-top: 20px;
                padding-top: 10px;
                border-top: 1px solid #e0e0e0;
                font-size: 14px;
                color: #777;
            }
            @media (max-width: 600px) {
                .container {
                    padding: 10px;
                    margin: 10px 15px;
                }
                h1 {
                    font-size: 20px;
                }
                .content {
                    font-size: 14px;
                }
            }
        </style>
    </head>
    <body>
        <div class="container">
            <img src="' . $logoUrl . '" alt="Kanisa Langu Logo" class="logo"/>
            <h1>' . htmlspecialchars($title) . '</h1>
            <div class="content">
                <p>Dear ' .$prefix. ''. ucfirst($role) . ',</p>
                ' . $message . '
            </div>
            <div class="footer">
                <p>&copy; ' . date('Y') . ' Kanisa Langu. All rights reserved.</p>
            </div>
        </div>
    </body>
    </html>';

    // Send email to specified admin
    return mail($admin_email, $subject, $email_message, $headers);
}

function sendFeedbackNotification($feedbackType, $subject, $message, $submittedBy = 'Head Parish Admin') {
    // $to_email = "feedback@kanisalangu.sewmrtechnologies.com";
    $to_email = "davidfaustinempinzile@gmail.com";
    $from_email = "info@kanisalangu.sewmrtechnologies.com";
    $sender_name = "Kanisa Langu";

    $headers = "From: $sender_name <$from_email>\r\n";
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    $logoUrl = 'https://kanisalangu.sewmrtechnologies.com/logo.png'; 

    $email_subject = "New Feedback Received: " . htmlspecialchars($subject);

    $email_message = '
    <html>
    <head>
        <title>New Feedback Received</title>
        <style>
            body { font-family: Arial, sans-serif; background: #f9f9f9; color: #333; }
            .container { max-width: 600px; margin: 20px auto; background: #fff; padding: 20px; border-radius: 6px; box-shadow: 0 0 8px rgba(0,0,0,0.1);}
            .logo { display: block; margin: 0 auto 20px; max-width: 150px; }
            h1 { text-align: center; color: #007bff; }
            p { font-size: 16px; line-height: 1.5; }
            .footer { text-align: center; font-size: 12px; color: #aaa; margin-top: 20px; }
        </style>
    </head>
    <body>
        <div class="container">
            <img src="' . $logoUrl . '" alt="Kanisa Langu Logo" class="logo" />
            <h1>New Feedback Submitted</h1>
            <p><strong>Type:</strong> ' . htmlspecialchars(ucfirst($feedbackType)) . '</p>
            <p><strong>Subject:</strong> ' . htmlspecialchars($subject) . '</p>
            <p><strong>Message:</strong><br />' . nl2br(htmlspecialchars($message)) . '</p>
            <p><em>Submitted by: ' . htmlspecialchars($submittedBy) . '</em></p>
            <div class="footer">&copy; ' . date('Y') . ' Kanisa Langu</div>
        </div>
    </body>
    </html>';

    return mail($to_email, $email_subject, $email_message, $headers);
}


function getAdminPrefix($target){
    // SQL query string placeholder
    $prefix = "";
    
    // Choose the query based on the target value
    switch ($target) {
        case 'head-parish':
            $prefix = "Head Parish ";
            break;

        case 'sub-parish':
            $prefix = "Sub Parish ";
            break;

        case 'community':
            $prefix = "Community ";
            break;

        case 'group':
        case 'groups':
            $prefix = "Group ";
            break;

        default:
            // If the target does not match any case, return null
            return null;
    }
    
    // Return the email if found, otherwise return null
    return $prefix ?: null;
}


function getAdminEmailByTargetAndRole($conn, $target, $role, $adminId) {
    // SQL query string placeholder
    $sql = "";
    
    // Choose the query based on the target value
    switch ($target) {
        case 'head-parish':
            $sql = "SELECT head_parish_admin_email AS email 
                    FROM head_parish_admins 
                    WHERE head_parish_admin_role = ? 
                    AND head_parish_id = ? 
                    LIMIT 1";
            break;

        case 'sub-parish':
            $sql = "SELECT sub_parish_admin_email AS email 
                    FROM sub_parish_admins 
                    WHERE sub_parish_admin_role = ? 
                    AND sub_parish_id = ? 
                    LIMIT 1";
            break;

        case 'community':
            $sql = "SELECT community_admin_email AS email 
                    FROM community_admins 
                    WHERE community_admin_role = ? 
                    AND community_id = ? 
                    LIMIT 1";
            break;

        case 'group':
        case 'groups':
            $sql = "SELECT group_admin_email AS email 
                    FROM group_admins 
                    WHERE group_admin_role = ? 
                    AND group_id = ? 
                    LIMIT 1";
            break;

        default:
            // If the target does not match any case, return null
            return null;
    }

    // Prepare and execute the statement
    $stmt = $conn->prepare($sql);
    if ($stmt === false) {
        // Error in preparing the statement
        return null;
    }

    // Bind parameters: role and the corresponding ID
    $stmt->bind_param("si", $role, $adminId);
    $stmt->execute();
    $stmt->bind_result($email);
    $stmt->fetch();
    $stmt->close();

    // Return the email if found, otherwise return null
    return $email ?: null;
}



function sendHarambeeContributionEmail($conn, $amount, $member, $contribution_date, $target, $harambee_id, $default_email = 'info@kanisalangu.sewmrtechnologies.com') {
    $from_email = "info@kanisalangu.sewmrtechnologies.com";
    $sender_name = "Kamati ya Harambee"; 
    $subject = "Mchango wa Harambee umepokelewa";
    $headers = "From: $sender_name <$from_email>\r\n"; 
    $headers .= "MIME-Version: 1.0\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";

    // Extract member information
    $member_id = $member['member_id'] ?? '[No ID]';
    $first_name = $member['first_name'] ?? '[No First Name]';
    $last_name = $member['last_name'] ?? '';
    $group_name = $member['group_name'] ?? null;
    $phone = $member['phone'] ?? '';
    $member_email = $member['email'] ?? null;
    $total_contribution = $member['total_contribution'] ?? 0.00;
    $target_amount = $member['target_amount'] ?? null;
    $harambee_description = $member['harambee_description'] ?? '[Harambee Description Missing]';

    $balance = $target_amount !== null ? ($target_amount - $total_contribution) : null;
    $recipient_name = $group_name ?: "$first_name";

    // Fetch accountant details
    $identifier_type = 'harambee';
    $accountantDetails = getHarambeeAccountant($conn, $target, $harambee_id, $identifier_type);
    // Accountant name removed intentionally
    $accountant_phone = $accountantDetails['phone'] ?? '';

    // Prepare dynamic text (same as SMS logic)
    $today = date('Y-m-d');
    $dateText = $today === $contribution_date 
        ? 'Leo tumepokea' 
        : 'Tarehe ' . date('d-m-Y', strtotime($contribution_date)) . ' tulipokea';

    $targetText = $group_name 
        ? 'Ombi letu la Harambee kwenu ni Shs.' 
        : 'Ombi letu la Harambee ni Shs.';

    $blessingText = $group_name ? 'awabariki' : 'akubariki';

    // Balance label
    if ($balance !== null) {
        $remainingLabel = $balance < 0 ? 'Zidio' : 'Salio';
        $remainingAmountFormatted = number_format(abs($balance));
    }

    // HTML message with styling
    $logoUrl = 'https://kanisalangu.sewmrtechnologies.com/logo.png'; 

    $message = '
    <html>
    <head>
        <title>Harambee Contribution Recorded</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                background-color: #f9f9f9;
                margin: 0;
                padding: 20px;
                color: #333;
            }
            .container {
                max-width: 600px;
                background: #fff;
                margin: 0 auto;
                padding: 30px;
                border-radius: 8px;
                box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            }
            .logo {
                display: block;
                margin: 0 auto 20px;
                max-width: 120px;
            }
            h1 {
                text-align: center;
                color: #2c3e50;
                margin-bottom: 30px;
                font-weight: 600;
            }
            .content p {
                font-size: 16px;
                line-height: 1.5;
                margin: 10px 0;
            }
            .recipient-name {
                font-weight: bold;
                color: #2c3e50;
            }
            strong {
                color: #e74c3c;
            }
            .footer {
                text-align: center;
                margin-top: 30px;
                font-size: 14px;
                color: #999;
                border-top: 1px solid #eee;
                padding-top: 15px;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <img src="' . $logoUrl . '" alt="Kanisa Langu Logo" class="logo" />
            <h1>Mchango wa Harambee Umepokelewa</h1>
            <div class="content">
                <p>Shalom <span class="recipient-name">' . htmlspecialchars($recipient_name) . '</span>,</p>';

    if ($target_amount !== null) {
        $message .= "<p>$targetText <strong>" . number_format($target_amount, 0) . "/=</strong></p>";
    }

    $message .= "<p>$dateText <strong>" . number_format($amount, 0) . "/=</strong></p>";
    $message .= "<p>Jumla taslimu hadi sasa: <strong>" . number_format($total_contribution, 0) . "/=</strong></p>";

    if ($target_amount !== null) {
        $message .= "<p>$remainingLabel: <strong>{$remainingAmountFormatted}/=</strong></p>";
    }

    $message .= "<p>Mungu $blessingText.</p>";

    if (!empty($accountant_phone)) {
        $message .= "<p><br>M/Hazina<br>$accountant_phone</p>";
    }

    $message .= '
            </div>
            <div class="footer">
                <p>&copy; ' . date('Y') . ' Kanisa Langu.</p>
            </div>
        </div>
    </body>
    </html>';

    $email_to_send = $member_email ? $member_email : $default_email;

    return mail($email_to_send, $subject, $message, $headers);
}


function notifyMemberAssignmentBySMS($conn, $member_id, $harambee_group_id, $target, $isMrAndMrs = false, $mrAndMrsName = null) {
    // Load member details
    $harambee_group_member = getMemberDetails($conn, $member_id);
    if ($harambee_group_member->num_rows === 0) {
        error_log("Member details could not be fetched for ID: $member_id");
        return false;
    }
    $harambee_group_member = $harambee_group_member->fetch_assoc(); // Fetch associative array

    // Load Harambee group details
    $harambee_group = getHarambeeGroupInfo($conn, $target, $harambee_group_id);
    if (!$harambee_group) {
        error_log("Harambee group details could not be fetched for ID: $harambee_group_id");
        return false;
    }

    // Prepare SMS credentials for the group member
    $smsInfo = get_sms_credentials($conn, $harambee_group_member['head_parish_id']);
    if (!$smsInfo) {
        error_log("No SMS credentials found for member ID: " . $harambee_group_member['member_id']);
        return false;
    }

    // Obtain Accountant Info
    $accountantDetails = getHarambeeAccountant($conn, $target, $harambee_group_id, 'harambee_group');
    $accountant_name = $accountantDetails ? $accountantDetails['first_name'] : '';
    $accountant_phone = $accountantDetails ? $accountantDetails['phone'] : '';

    // Extract member information
    $first_name = ucfirst(strtolower($harambee_group_member['first_name'] ?? '[No First Name]'));
    $last_name = $harambee_group_member['last_name'] ?? '[No Last Name]';
    $phone = $harambee_group_member['phone'] ?? '255678048567'; // Default fallback
    $identifier = $isMrAndMrs ? $mrAndMrsName : $first_name;

    // Prepare the welcome message
    $welcome_message = $isMrAndMrs 
        ? "Mmeunganishwa kwenye kundi la Harambee: {$harambee_group['group_name']}.\n" 
        : "Umeunganishwa kwenye kundi la Harambee: {$harambee_group['group_name']}.\n";

    // Prepare the full SMS message
    $message = "Shalom {$identifier}!\n";
    $message .= $welcome_message;
    $message .= "Lengo la kundi: " . $harambee_group['harambee_group_target'] . "/=\n";
    $message .= "Mungu akubariki.\nM/Hazina \n{$accountant_phone}";

    // Use the quick send approach
    $smsClient = new SewmrSMSClient($smsInfo['api_token'], $smsInfo['sender_id']);
    $response = $smsClient->sendQuickSMS(null, $message, [$phone]);

    // Handle response
    if (!isset($response['success']) || $response['success'] !== true) {
        $errorMessage = $response['message'] ?? "Unknown error";
        error_log("Failed to send SMS to {$phone}: $errorMessage");
        return false;
    }

    return true;
}



function sendHarambeeReminder($conn, $member_id) {
    // Load member details
    $memberResult = getMemberDetails($conn, $member_id);
    if (!$memberResult || $memberResult->num_rows === 0) {
        error_log("Member details not found for ID: $member_id");
        return false;
    }
    $member = $memberResult->fetch_assoc();

    // Get SMS credentials
    $smsInfo = get_sms_credentials($conn, $member['head_parish_id']);
    if (!$smsInfo) {
        error_log("No SMS credentials found for member ID: $member_id");
        return false;
    }

    // Format member name and phone
    $first_name = ucfirst(strtolower($member['first_name'] ?? '[Jina]'));
    $phone = $member['phone'] ?? null;
    $envelope_number = !empty($member['envelope_number']) ? $member['envelope_number'] : null;

    if (!$phone) {
        error_log("No phone number found for member ID: $member_id");
        return false;
    }

    // Compose the message
    $message = "Shalom {$first_name}!\n";
    $message .= "Tunaendelea kukukaribisha kutoa Sadaka yako ya Harambee kesho Jumapili 11/05/2025.\n";
    if ($envelope_number) {
        $message .= "Taja namba yako ya bahasha {$envelope_number} kwa karani.\n";
    }
    $message .= "Mungu akubariki.\n";
    $message .= "M/Kiti wa Harambee.\n";
    $message .= "Ephraem L. Maina.\n";
    $message .= "0754476129";

    // Use quick send SMS
    $smsClient = new SewmrSMSClient($smsInfo['api_token'], $smsInfo['sender_id']);
    $response = $smsClient->sendQuickSMS(null, $message, [$phone]);

    // Handle response
    if (!isset($response['success']) || $response['success'] !== true) {
        $errorMessage = $response['message'] ?? "Unknown error";
        error_log("Failed to send SMS to {$phone}: $errorMessage");
        return false;
    }

    return true;
}


function getHarambeeGroupIdsByHarambeeId($conn, $harambee_id, $target, $sub_parish_id = null) {
    // Define the table based on the target type
    $table = '';
    switch ($target) {
        case 'head-parish':
            $table = 'head_parish_harambee_groups';
            break;
        case 'sub-parish':
            $table = 'sub_parish_harambee_groups';
            break;
        case 'community':
            $table = 'community_harambee_groups';
            break;
        case 'group':
        case 'groups':
            $table = 'groups_harambee_groups';
            break;
        default:
            return ["success" => false, "message" => "Invalid target type"];
    }

    // Prepare the SQL query with optional sub_parish_id condition
    $query = "SELECT harambee_group_id FROM $table WHERE harambee_id = ?";
    $params = [$harambee_id];
    $paramTypes = "i"; // Parameter type for harambee_id

    if (!is_null($sub_parish_id)) {
        $query .= " AND sub_parish_id = ?";
        $params[] = $sub_parish_id;
        $paramTypes .= "i"; // Add parameter type for sub_parish_id
    }

    // Prepare the SQL statement
    $stmt = $conn->prepare($query);
    if (!$stmt) {
        return ["success" => false, "message" => "Failed to prepare statement: " . $conn->error];
    }

    // Bind parameters dynamically
    $stmt->bind_param($paramTypes, ...$params);

    // Execute the query
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch all harambee group IDs
    $harambee_group_ids = [];
    while ($row = $result->fetch_assoc()) {
        $harambee_group_ids[] = $row['harambee_group_id'];
    }

    $stmt->close();

    // Return the list of harambee group IDs
    return $harambee_group_ids;
}


function getHarambeeGroupsContributions($conn, $harambee_id, $target, $sub_parish_id = null) {
    // Fetch all Harambee group IDs for the given Harambee ID and target
    $harambeeGroups = getHarambeeGroupIdsByHarambeeId($conn, $harambee_id, $target, $sub_parish_id);

    $result = [];
    foreach ($harambeeGroups as $harambee_group_id) {
        // Get Harambee group info
        $group_info = getHarambeeGroupInfo($conn, $target, $harambee_group_id);
        if (!$group_info) {
            continue; // Skip if group info is missing
        }
        
        $group_name = $group_info['group_name'];
        $group_description = $group_info['harambee_group_description'];
        $group_target = str_replace([','], '', $group_info['harambee_group_target']);
        $group_start_date = $group_info['date_created'];
        
        
        // Convert it back to a numeric value
        $group_target = (float) $group_target;

        // Get member IDs for the Harambee group
        $member_ids = getHarambeeGroupMemberIds($conn, $target, $harambee_group_id);
        if (empty($member_ids)) {
            $total_contribution = 0;
        } else {
            // Calculate total contributions for the group members
            $contributions = getHarambeeGroupContributions($conn, $harambee_id, $member_ids, $group_start_date, $target);
            
            // Sum the total contribution
            $total_contribution = 0;
            foreach ($contributions as $contribution) {
                $total_contribution += $contribution['total_contribution'];
            }
        }



        // Calculate balance
        $balance = $group_target - $total_contribution;

        // Append group details to the result
        $result[] = [
            "group_name" => $group_name,
            "group_description" => $group_description,
            "group_target" => $group_target,
            "total_contribution" => $total_contribution,
            "balance" => $balance,
            "date_created" => $group_start_date
        ];
    }

    return $result;
}


function getHarambeeAccountant($conn, $target, $identifier_id, $identifier_type) {
    $admin_table = "";
    $harambee_distribution_table = "";
    $harambee_group_table = "";
    $accountantFirstName = '';
    $accountantPhone = '';
    $fullname_column = '';
    $phone_column = '';
    $role_column = '';

    // Determine the tables based on the target type
    switch ($target) {
        case 'head-parish':
            $admin_table = "head_parish_admins";
            $harambee_distribution_table = "head_parish_harambee_distribution";
            $harambee_group_table = "head_parish_harambee_groups";
            $fullname_column = "head_parish_admin_fullname";
            $phone_column = "head_parish_admin_phone";
            $role_column = 'head_parish_admin_role';
            break;

        case 'sub-parish':
            $admin_table = "sub_parish_admins";
            $harambee_distribution_table = "sub_parish_harambee_distribution"; 
            $harambee_group_table = "sub_parish_harambee_groups";
            $fullname_column = "sub_parish_admin_fullname";
            $phone_column = "sub_parish_admin_phone";
            $role_column = 'sub_parish_admin_role';
            break;

        case 'community':
            $admin_table = "community_admins";
            $harambee_distribution_table = "community_harambee_distribution"; 
            $harambee_group_table = "community_harambee_groups";
            $fullname_column = "community_admin_fullname";
            $phone_column = "community_admin_phone";
            $role_column = 'community_admin_role';
            break;

        case 'group':
        case 'groups':
            $admin_table = "group_admins";
            $harambee_distribution_table = "group_harambee_distribution"; 
            $harambee_group_table = "groups_harambee_groups";
            $fullname_column = "group_admin_fullname";
            $phone_column = "group_admin_phone";
            $role_column = 'group_admin_role';
            break;

        default:
            return false; 
    }

    // Initialize variables to hold IDs
    $harambee_id = null;
    $head_parish_id = 0;
    $sub_parish_id = 0;
    $community_id = 0;
    $group_id = 0;

    // Determine the process to get the necessary IDs based on identifier_type
    if ($identifier_type === 'harambee') {
        // Use identifier_id directly as harambee_id
        $harambee_id = $identifier_id;

    } elseif ($identifier_type === 'harambee_group') {
        // Get the harambee_id from the corresponding harambee_group table
        $queryHarambeeId = "
            SELECT h.harambee_id 
            FROM $harambee_group_table h 
            WHERE h.harambee_group_id = $identifier_id 
            LIMIT 1";

        $resultHarambeeId = $conn->query($queryHarambeeId);
        if ($row = $resultHarambeeId->fetch_assoc()) {
            $harambee_id = $row['harambee_id'];
        }
    }

    // Column query to retrieve IDs based on target
    switch($target) {
        case 'head-parish':
        case 'sub-parish':
            $column_query = "
                SELECT head_parish_id, sub_parish_id 
                FROM $harambee_distribution_table 
                WHERE harambee_id = $harambee_id 
                LIMIT 1";
            break;

        case 'community':
            $column_query = "
                SELECT head_parish_id, sub_parish_id, community_id 
                FROM $harambee_distribution_table 
                WHERE harambee_id = $harambee_id 
                LIMIT 1";
            break;

        case 'group':
        case 'groups':
            $column_query = "
                SELECT head_parish_id, group_id 
                FROM $harambee_distribution_table 
                WHERE harambee_id = $harambee_id 
                LIMIT 1";
            break;

        default:
            return false;
    }

    // Start session if not already started
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Execute the column query and fetch IDs
    if (!empty($column_query)) {
        $resultColumns = $conn->query($column_query);
        if ($row = $resultColumns->fetch_assoc()) {
            // Use head_parish_id from session if available, otherwise from SQL result
            if (isset($_SESSION['head_parish_id'])) {
                $head_parish_id = $_SESSION['head_parish_id'];
            } elseif (isset($row['head_parish_id'])) {
                $head_parish_id = $row['head_parish_id'];
                error_log($head_parish_id);
            }
            if (isset($row['sub_parish_id'])) {
                $sub_parish_id = $row['sub_parish_id'];
            }
            if (isset($row['community_id'])) {
                $community_id = $row['community_id'];
            }
            if (isset($row['group_id'])) {
                $group_id = $row['group_id'];
            }
        }
    }
    
    // Final fallback: fetch head_parish_id from harambee table if still missing
    if (empty($head_parish_id) && $harambee_id) {
        $head_parish_id = getHeadParishIdFromHarambee($conn, $harambee_id);
    }
    $query = "
        SELECT $fullname_column, $phone_column 
        FROM $admin_table 
        WHERE head_parish_id = $head_parish_id ";

    // Append filters based on the target type
    // switch ($target) {
    //     case 'sub-parish':
    //         $query .= "AND sub_parish_id = $sub_parish_id ";
    //         break;
    //     case 'community':
    //         $query .= "AND sub_parish_id = $sub_parish_id AND community_id = $community_id ";
    //         break;
    //     case 'group':
    //         $query .= "AND group_id = $group_id "; 
    //         break;
    // }

    $query .= "AND $role_column = 'accountant' LIMIT 1"; 
    
    // error_log($column_query);
    // error_log($query);

    $result = $conn->query($query);
    if ($row = $result->fetch_assoc()) {
        // Split the full name and get the first name, capitalize the first letter and lower the rest
        $fullName = $row[$fullname_column];
        $names = explode(" ", $fullName);
        $accountantFirstName = ucfirst(strtolower($names[0])); // Capitalize the first name
        $accountantPhone = $row[$phone_column];
        // Replace '255' at the beginning with '0'
        if (substr($accountantPhone, 0, 3) === '255') {
            $accountantPhone = '0' . substr($accountantPhone, 3);
        }
    }

    return [
        'first_name' => $accountantFirstName,
        'phone' => $accountantPhone
    ]; // Return both first name and phone number of the accountant admin
}

function getAdminPhoneAndFirstNameByRole($conn, $head_parish_id, $role) {
    $query = "
        SELECT head_parish_admin_phone, head_parish_admin_fullname 
        FROM head_parish_admins 
        WHERE head_parish_id = ? 
        AND head_parish_admin_role = ? 
        LIMIT 1
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("is", $head_parish_id, $role); 
    $stmt->execute();
    $result = $stmt->get_result();

    $adminPhone = '';
    $adminFirstName = '';

    if ($row = $result->fetch_assoc()) {
        $adminPhone = $row['head_parish_admin_phone'];

        // Convert 255xxx to 0xxx
        if (substr($adminPhone, 0, 3) === '255') {
            $adminPhone = '0' . substr($adminPhone, 3);
        }

        // Get first name
        $names = explode(" ", $row['head_parish_admin_fullname']);
        $adminFirstName = ucfirst(strtolower($names[0]));
    }

    return [
        'first_name' => $adminFirstName,
        'phone' => $adminPhone
    ];
}


function getAccountantPhoneByHeadParish($conn, $head_parish_id) {
    $query = "
        SELECT head_parish_admin_phone, head_parish_admin_fullname 
        FROM head_parish_admins 
        WHERE head_parish_id = ? 
        AND head_parish_admin_role = 'accountant' 
        LIMIT 1
    ";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $head_parish_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $accountantPhone = '';
    $accountantFirstName = '';

    if ($row = $result->fetch_assoc()) {
        $accountantPhone = $row['head_parish_admin_phone'];

        // Convert international format 255xxx to local 0xxx
        if (substr($accountantPhone, 0, 3) === '255') {
            $accountantPhone = '0' . substr($accountantPhone, 3);
        }

        // Extract and format first name
        $names = explode(" ", $row['head_parish_admin_fullname']);
        $accountantFirstName = ucfirst(strtolower($names[0]));
    }

    return [
        'first_name' => $accountantFirstName,
        'phone' => $accountantPhone
    ];
}

function getHeadParishIdFromHarambee($conn, $harambee_id) {
    $query = "SELECT head_parish_id FROM head_parish_harambee WHERE harambee_id = $harambee_id LIMIT 1";
    $result = $conn->query($query);
    if ($row = $result->fetch_assoc()) {
        return (int) $row['head_parish_id'];
    }
    return 0;
}


function getDistributedAmount($conn, $target, $reference_key, $harambee_id) {
    $harambee_distribution_table = "";
    $column_name = "";  // Column name to filter based on the target

    // Determine the distribution table and the column name based on the target
    switch ($target) {
        case 'head-parish':
            $harambee_distribution_table = "head_parish_harambee_distribution";
            $column_name = "sub_parish_id";  // head-parish uses sub_parish_id
            break;
        case 'sub-parish':
            $harambee_distribution_table = "sub_parish_harambee_distribution";
            $column_name = "sub_parish_id";  // sub-parish uses sub_parish_id
            break;
        case 'community':
            $harambee_distribution_table = "community_harambee_distribution";
            $column_name = "community_id";  // community uses community_id
            break;
        case 'group':
        case 'groups':
            $harambee_distribution_table = "group_harambee_distribution";
            $column_name = "group_id";  // group uses group_id
            break;
        default:
            return 0;  // Invalid target, return 0
    }

    // Build the query to get the distributed amount for the given reference_key and harambee_id
    $query = "
        SELECT amount AS distributed_amount
        FROM $harambee_distribution_table
        WHERE $column_name = '$reference_key' AND harambee_id = '$harambee_id'
        LIMIT 1
    ";

    // Execute the query
    $result = $conn->query($query);

    // Check if the query returns a result
    if ($result && $row = $result->fetch_assoc()) {
        // Return the distributed amount
        return $row['distributed_amount'];
    }

    // Return 0 if no result is found or query fails
    return 0;
}


function sendHarambeeContributionSMSOld($conn, $amount, $member, $contribution_date, $target, $harambee_id) {
    $date = $contribution_date;
    $smsInfo = get_sms_credentials($conn, $member['head_parish_id']);

    if (!$smsInfo) {
        error_log("No SMS credentials found for member ID: " . $member['member_id']);
        return false;
    }

    $accountantDetails = getHarambeeAccountant($conn, $target, $harambee_id, 'harambee');
    $accountant_name = $accountantDetails['first_name'] ?? '';
    $accountant_phone = $accountantDetails['phone'] ?? '';

    $first_name = $member['first_name'] ?? '[No First Name]';
    $last_name = $member['last_name'] ?? '[No Last Name]';
    $group_name = $member['group_name'] ?? null;
    $phone = $member['phone'] ?? '';
    if ($phone === null || trim($phone) === '') {
        $phone = '255678048567';
    }
    $total_contribution = $member['total_contribution'] ?? 0.00;
    $target_amount = $member['target_amount'] ?? null;

    $today = date('Y-m-d');
    $dateText = $today === $date ? 'Leo tumepokea' : 'Tarehe ' . date('d-m-Y', strtotime($date)) . ' tulipokea';
    $balance = $target_amount !== null ? ($target_amount - $total_contribution) : null;
    $recipientName = $group_name ?: $first_name;
    $targetText = $group_name ? 'Ombi letu la Harambee kwenu ni shs. ' : 'Ombi letu la Harambee ni shs. ';
    $blessingText = $group_name ? 'awabariki' : 'akubariki';

    if ($target_amount != 0) {
        $message = "Shalom $recipientName!\n$targetText" . number_format($target_amount, 0) . "/=\n{$dateText} " . number_format($amount, 0) . "/=\nJumla taslimu " . number_format($total_contribution, 0) . "/=\n";

        if ($balance < 0) {
            $remainingLabel = 'Zidio';
            $remainingAmountFormatted = number_format(abs($balance));
        } else {
            $remainingLabel = 'Salio';
            $remainingAmountFormatted = number_format($balance);
        }

        $message .= "$remainingLabel $remainingAmountFormatted/=\nMungu $blessingText.\nM/Hazina\n{$accountant_phone}\n";
    } else {
        $message = "Shalom $recipientName!\n{$dateText} " . number_format($amount, 0) . "/=\nJumla taslimu " . number_format($total_contribution, 0) . "/=\nMungu akubariki.\nM/Hazina\n{$accountant_phone}\n";
    }

    // Use quick send client
    $smsClient = new SewmrSMSClient($smsInfo['api_token'], $smsInfo['sender_id']);
    $response = $smsClient->sendQuickSMS(null, $message, [$phone]);

    if (!isset($response['success']) || $response['success'] !== true) {
        $errorMessage = $response['message'] ?? "Unknown error";
        error_log("Failed to send SMS to {$phone}: $errorMessage");
        return false;
    }

    return true;
}


function sendHarambeeContributionSMS($conn, $amount, $member, $contribution_date, $target, $harambee_id) {
    $date = $contribution_date;
    $smsInfo = get_sms_credentials($conn, $member['head_parish_id']);

    if (!$smsInfo) {
        error_log("No SMS credentials found for member ID: " . $member['member_id']);
        return false;
    }

    $harambeeDetails = get_harambee_details($conn, $harambee_id, $target);
    
    $harambeeTitle = '';
    if ($harambeeDetails && !empty($harambeeDetails['description'])) {
        $harambeeTitle = strtoupper($harambeeDetails['description']) . "\n";
    }
    $identifier_type = 'harambee';
    $accountantDetails = getHarambeeAccountant($conn, $target, $harambee_id, $identifier_type);

    $accountant_name = $accountantDetails['first_name'] ?? '';
    $accountant_phone = $accountantDetails['phone'] ?? '';

    $first_name = $member['first_name'] ?? '[No First Name]';
    $last_name = $member['last_name'] ?? '[No Last Name]';
    $group_name = $member['group_name'] ?? null;
    $phone = $member['phone'] ?? '255678048567';
    $total_contribution = $member['total_contribution'] ?? 0.00;
    $target_amount = $member['target_amount'] ?? null;
    $head_parish_name = $member['head_parish_name'] ?? null;
    $harambee_description = $member['harambee_description'] ?? null;

    $today = date('Y-m-d');
    $dateText = $today === $date ? 'Leo tumepokea' : 'Tarehe ' . date('d-m-Y', strtotime($date)) . ' tulipokea';
    $balance = $target_amount !== null ? ($target_amount - $total_contribution) : null;
    $recipientName = $group_name ?: $first_name;
    $targetText = $group_name ? 'Ombi letu la Harambee kwenu ni shs. ' : 'Ombi letu la Harambee ni shs. ';
    $blessingText = $group_name ? 'awabariki' : 'akubariki';
    
    // Dynamic pronouns depending on group or individual
    $sadakaPronoun   = $group_name ? 'zenu' : 'yako';
    $malizaPronoun   = $group_name ? 'mmemaliza' : 'umemaliza';
    $uwianoPronoun   = $group_name ? 'wenu' : 'wako';
    $pokeePronoun    = $group_name ? 'mpokee' : 'upokee';
    $mnavyoPronoun   = $group_name ? 'mnavyomtumikia' : 'unavyomtumikia';
    $afyaPronoun     = $group_name ? 'awajalie' : 'akujalie';
    $kaziPronoun     = $group_name ? 'zenu' : 'yako';
    $ziadaPronoun    = $group_name ? 'mkiwa' : 'ukiwa';

    if ($target_amount != 0) {
        $prefixmessage = "Shalom $recipientName!\n$targetText" . number_format($target_amount, 0) . "/=\n{$dateText} "
                     . number_format($amount, 0) . "/=\nJumla taslimu " . number_format($total_contribution, 0) . "/=\n";
    
        // Check if contribution exactly matches target
        if ($total_contribution == $target_amount) {
            // Fully completed contribution
            $message = "$prefixmessage!\nKwa sadaka $sadakaPronoun ya leo, $malizaPronoun uwiano $uwianoPronoun.\n"
                     . "Ofisi ya Mchungaji Kiongozi na M/Kiti wa Harambee, tunaomba $pokeePronoun Shukrani zetu kwa jinsi $mnavyoPronoun Mungu kwa moyo.\n"
                     . "Mungu $blessingText, $afyaPronoun afya njema, na abariki kazi za mikono $kaziPronoun.\n"
                     . "M/Hazina\n{$accountant_phone}\n";
        }
        // Check if contribution exceeds target
        elseif ($total_contribution > $target_amount) {
            $extraAmount = $total_contribution - $target_amount;
    
            $message = "$prefixmessage\nKwa sadaka $sadakaPronoun ya leo, $malizaPronoun uwiano $uwianoPronoun $ziadaPronoun na ziada ya  "
                     . number_format($extraAmount, 0) . "/=\n"
                     . "Ofisi ya Mchungaji Kiongozi na M/Kiti wa Harambee, tunaomba $pokeePronoun Shukrani zetu kwa jinsi $mnavyoPronoun Mungu kwa moyo.\n"
                     . "Mungu $blessingText, $afyaPronoun afya njema, na abariki kazi za mikono $kaziPronoun.\n"
                     . "M/Hazina\n{$accountant_phone}\n";
        }
        // Default case — previous logic when target not reached yet
        else {
            $message = "Shalom $recipientName!\n$targetText" . number_format($target_amount, 0) . "/=\n{$dateText} "
                     . number_format($amount, 0) . "/=\nJumla taslimu " . number_format($total_contribution, 0) . "/=\n";
    
            if ($balance < 0) {
                $remainingLabel = 'Zidio';
                $remainingAmountFormatted = number_format(abs($balance));
            } else {
                $remainingLabel = 'Salio';
                $remainingAmountFormatted = number_format($balance);
            }
    
            $message .= "$remainingLabel $remainingAmountFormatted/=\nMungu $blessingText.\nM/Hazina\n{$accountant_phone}\n";
        }
    } else {
        $message = "Shalom $recipientName!\n{$dateText} " . number_format($amount, 0) . "/=\nJumla taslimu " . number_format($total_contribution, 0) . "/=\nMungu akubariki.\nM/Hazina\n{$accountant_phone}\n";
    }

    $smsClient = new SewmrSMSClient($smsInfo['api_token'], $smsInfo['sender_id']);
    
    $finalMessage = $harambeeTitle . $message;
    $response = $smsClient->sendQuickSMS(null, $finalMessage, [$phone]);
    
    if (!isset($response['success']) || $response['success'] !== true) {
        $errorMessage = $response['message'] ?? "Unknown error";
        error_log("Failed to send SMS to {$phone}: $errorMessage");
        return false;
    }

    return true;
}

function sendEnvelopeContributionSMS($conn, $memberId, $amount, $contributionDate) {
    $year = date('Y', strtotime($contributionDate));
    $envelopeData = fetchMemberEnvelopeData($conn, $memberId, $year);

    $memberDetails = getMemberDetails($conn, $memberId)->fetch_assoc();
    if (!$memberDetails) {
        error_log("Member not found: $memberId");
        return false;
    }

    $headParishId = $memberDetails['head_parish_id'] ?? 0;
    if (!$headParishId) {
        error_log("No head parish ID found for member ID: $memberId");
        return false;
    }

    // Get accountant contact
    $accountant = getAccountantPhoneByHeadParish($conn, $headParishId);
    $accountant_phone = $accountant['phone'] ?? '255678048567';
    // Get SMS credentials
    $smsInfo = get_sms_credentials($conn, $memberDetails['head_parish_id']);
    if (!$smsInfo) {
        error_log("No SMS credentials found for member ID: $memberId");
        return false;
    }

    // First name only, lower then ucwords
    $firstName = isset($memberDetails['first_name']) ? ucwords(strtolower($memberDetails['first_name'])) : 'Mpendwa';

    // Phone fallback
    $phone = $memberDetails['phone'] ?? '255678048567';

    // Date text
    $today = date('Y-m-d');
    $dateText = $today === $contributionDate 
        ? 'Leo tumepokea' 
        : 'Tarehe ' . date('d-m-Y', strtotime($contributionDate)) . ' tulipokea';

    // Target, total, balance
    $target = $envelopeData['yearly_envelope_target'];
    $total = $envelopeData['total_envelope_contribution'];
    $balance = $target - $total;

    // SMS content
    $message = "Shalom $firstName!\n";
    if ($target > 0) {
        $message .= "Ahadi yako ya Bahasha ni: " . number_format($target, 0) . "/=\n";
    }else{
        $message .= "Unakumbushwa kuweka ahadi ya Sadaka ya Bahasha.\n";
    }

    $message .= "$dateText " . number_format($amount, 0) . "/=\n";
    $message .= "Jumla taslimu " . number_format($total, 0) . "/=\n";

    if ($target > 0) {
        $label = $balance < 0 ? 'Zidio' : 'Salio';
        $message .= "$label " . number_format(abs($balance), 0) . "/=\n";
    }

    $message .= "Mungu akubariki.\nM/Hazina\n{$accountant_phone}\n";
    
    // Send SMS using quick send
    $smsClient = new SewmrSMSClient($smsInfo['api_token'], $smsInfo['sender_id']);
    $response = $smsClient->sendQuickSMS(null, $message, [$phone]);
    
    if (!isset($response['success']) || !$response['success']) {
        error_log("Failed to send SMS to {$phone}: " . ($response['message'] ?? 'Unknown error'));
        return false;
    }

    return true;
}


function sendOfferingSMS($conn, $headParishId, $member_id, $revenueStreamId, $amount) {
    $result = getMemberDetails($conn, $member_id);
    if (!$result || $result->num_rows === 0) {
        error_log("Member not found with ID: $member_id");
        return false;
    }

    $member = $result->fetch_assoc();

    $revenueStreamName = getRevenueStreamName($conn, $headParishId, $revenueStreamId);

    $phone = $member['phone'] ?? '255678048567';
    $recipientName = $member['first_name'] ?? 'Mpendwa';

    $message = "Shalom $recipientName,\nTumepokea sadaka yako ya $revenueStreamName Shs. " . number_format($amount, 0) . "/=.\nMungu akubariki.";

    $smsInfo = get_sms_credentials($conn, $member['head_parish_id']);
    if (!$smsInfo) {
        error_log("No SMS credentials found for member ID: " . $member_id);
        return false;
    }

    // Send offering SMS using quick send
    $smsClient = new SewmrSMSClient($smsInfo['api_token'], $smsInfo['sender_id']);
    $response = $smsClient->sendQuickSMS(null, $message, [$phone]);
    
    if (!isset($response['success']) || $response['success'] !== true) {
        $errorMessage = $response['message'] ?? "Unknown error";
        error_log("Failed to send offering SMS to {$phone}: $errorMessage");
        return false;
    }

    return true;
}


function sendHarambeeTargetSMS($conn, $member_id, $amount, $target, $harambee_id, $target_type, $group_name = null) {
    $result = getMemberDetails($conn, $member_id);
    if (!$result || $result->num_rows === 0) {
        error_log("Member not found with ID: $member_id");
        return false;
    }

    $member = $result->fetch_assoc();
    $phone = $member['phone'] ?? '255678048567';
    $recipientName = ($target_type === 'group' && $group_name) ? $group_name : ($member['first_name'] ?? 'Mpendwa');
    $thanksText = (($target_type === 'group' && $group_name) ? 'Tunawashukuru' : 'Tunakushukuru');
    $head_parish_id = $member['head_parish_id'];
    $smsInfo = get_sms_credentials($conn, $head_parish_id);

    if (!$smsInfo) {
        error_log("No SMS credentials found for head parish ID: " . $head_parish_id);
        return false;
    }


    $identifier_type = 'harambee';
    $accountantDetails = getHarambeeAccountant($conn, $target, $harambee_id, $identifier_type);
    $accountant_phone = $accountantDetails['phone'] ?? '';

    $formattedAmount = number_format($amount, 0);
    $message = "Shalom $recipientName!\n$thanksText kwa kupokea ombi letu la Harambee Shs. {$formattedAmount}/=\nMungu akubariki.\nM/Hazina\n{$accountant_phone}";

    // Send SMS using quick send
    $smsClient = new SewmrSMSClient($smsInfo['api_token'], $smsInfo['sender_id']);
    $response = $smsClient->sendQuickSMS(null, $message, [$phone]);
    
    // error_log("SMS API raw response: " . json_encode($response));
    
    if (!isset($response['success']) || $response['success'] !== true) {
        $errorMessage = $response['message'] ?? "Unknown error";
        error_log("Failed to send SMS to {$phone}: $errorMessage");
        return false;
    }

    return true;
}


function sendHarambeeTargetUpdateNotification($conn, $member_id, $current_target, $new_target, $target, $harambee_id, $isMrAndMrs = false, $mrAndMrsName = null, $target_difference) {
    // Retrieve member details
    $stmt = $conn->prepare("SELECT first_name, last_name, phone, head_parish_id, sub_parish_id FROM church_members WHERE member_id = ?");
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $member = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    // Check if the member details were retrieved successfully
    if (!$member) {
        error_log("No member details found for member ID: $member_id");
        return false;
    }
    
    
    // Obtain Accountant Info
    $identifier_type = 'harambee';
    $accountant_name = '';
    $accountant_phone = '';
    $accountantDetails = getHarambeeAccountant($conn, $target, $harambee_id, $identifier_type);
    // Check if the function returned valid results
    if ($accountantDetails) {
        $accountant_name = $accountantDetails['first_name'];
        $accountant_phone =  $accountantDetails['phone'];
    }

    // Get SMS credentials for the member
    $smsInfo = get_sms_credentials($conn, $member['head_parish_id']);

    if (!$smsInfo) {
        error_log("No SMS credentials found for member ID: $member_id");
        return false;
    }

    // Extract member information
    $first_name = ucfirst(strtolower($member['first_name']));
    $last_name = $member['last_name'];
    $phone = $member['phone'];

    $identifier = $isMrAndMrs ? $mrAndMrsName : $first_name;
    // Prepare the welcome message based on Mr and Mrs status
    $welcome_message = $isMrAndMrs 
        ? "Mungu awabariki kwa nyongeza ya Ahadi ya Harambee Shs. " 
        : "Mungu akubariki kwa nyongeza ya Ahadi ya Harambee Shs. ";
    // Compose the SMS message
    $message = "Shalom $identifier!\n" .
               "{$welcome_message}" .number_format($target_difference, 0) . "/=\n" .
               "M/Hazina\n$accountant_phone";

    // Send SMS using quick send
    $smsClient = new SewmrSMSClient($smsInfo['api_token'], $smsInfo['sender_id']);
    $response = $smsClient->sendQuickSMS(null, $message, [$phone]);
    
    if (!isset($response['success']) || $response['success'] !== true) {
        $errorMessage = $response['message'] ?? "Unknown error";
        error_log("Failed to send SMS to {$phone}: $errorMessage");
        return false;
    }

    return true; 
}



function notifyHarambeeGroupMembersBySMS($conn, $group_target, $totalHarambeeGroupContributions, $group_name, $harambee_description, $harambee_group_member, $contributing_member, $amount, $contribution_date,$target, $harambee_id, $isMrAndMrs = false, $mrAndMrsName = null) {
    // Get SMS credentials for the group member
    $smsInfo = get_sms_credentials($conn, $harambee_group_member['head_parish_id']);

    // Check if credentials were retrieved successfully
    if (!$smsInfo) {
        error_log("No SMS credentials found for member ID: " . $harambee_group_member['member_id']);
        return false; // No credentials found
    }


    // Obtain Accountant Info
    $identifier_type = 'harambee';
    $accountant_name = '';
    $accountant_phone = '';
    $accountantDetails = getHarambeeAccountant($conn, $target, $harambee_id, $identifier_type);
    // Check if the function returned valid results
    if ($accountantDetails) {
        $accountant_name = $accountantDetails['first_name'];
        $accountant_phone =  $accountantDetails['phone'];
    }
    
    // Extract member information
    $first_name = isset($harambee_group_member['first_name']) ? $harambee_group_member['first_name'] : '[No First Name]';
    $last_name = isset($harambee_group_member['last_name']) ? $harambee_group_member['last_name'] : '[No Last Name]';
    $phone = isset($harambee_group_member['phone']) ? $harambee_group_member['phone'] : '255678048567'; // Default if not found
    $total_contribution = isset($harambee_group_member['total_contribution']) ? $harambee_group_member['total_contribution'] : 0.00;


    // Get today's date in the same format as the contribution date
    $today = date('Y-m-d'); 
    
    $dateText = '';
    $extarText = '';
    // Determine contribution date message
    if ($contribution_date == $today) {
        $dateText = "Leo";  
        $extarText = $isMrAndMrs ? "wametoa": "ametoa";
    } else {
        $formattedDate = date('d/m/Y', strtotime($contribution_date));
        $dateText = "Tarehe {$formattedDate} "; 
        $extarText = $isMrAndMrs ? "walitoa": "alitoa";
    }
    
    $identifier = $isMrAndMrs ? $mrAndMrsName : ucfirst(strtolower($contributing_member['first_name']));

        
    // Prepare the message
    $message = "Shalom {$group_name}!\n";
    $message .= "Ombi letu la Harambee kwenu ni Shs. " . number_format($group_target, 0) . "/=\n";
    $message .= $dateText . " " . $identifier . " " . $extarText . " " . number_format($amount, 0) . "/=\n";
    $message .= "Jumla ya Taslimu Shs. " . number_format($totalHarambeeGroupContributions, 0) . "/=\n";

    // Calculate remaining balance
    $balance = $group_target - $totalHarambeeGroupContributions;
    $remainingLabel = $balance < 0 ? 'Zidio Shs.' : 'Salio Shs.';
    $remainingAmountFormatted = number_format(abs($balance), 0);

    $message .= "$remainingLabel $remainingAmountFormatted/=\nMungu awabariki.\nM/Hazina\n{$accountant_phone}\n";

    // Send SMS using quick send
    $smsClient = new SewmrSMSClient($smsInfo['api_token'], $smsInfo['sender_id']);
    $response = $smsClient->sendQuickSMS(null, $message, [$phone]);
    
    if (!isset($response['success']) || $response['success'] !== true) {
        $errorMessage = $response['message'] ?? "Unknown error";
        error_log("Failed to send SMS to {$phone}: $errorMessage");
        return false; // Return false if SMS failed
    }

    return true; 
}


function fetchMemberEnvelopeData($conn, $member_id, $year): array {
    // Initialize an array to hold results
    $results = [
        'total_envelope_contribution' => 0.00,
        'yearly_envelope_target' => 0.00,
        'total_annual_envelopes' => 0,
        'total_envelopes_until_today' => 0,
        'member_contributions_until_today' => 0,
    ];

    // Fetch total collected amount for the specified year
    $stmt_fetch_total_amount = $conn->prepare("
        SELECT SUM(amount) 
        FROM envelope_contribution 
        WHERE member_id = ? AND YEAR(contribution_date) = ? AND contribution_date <= CURRENT_DATE()
    ");
    if (!$stmt_fetch_total_amount) {
        error_log($conn->error);
        return $results;
    }

    $stmt_fetch_total_amount->bind_param("ii", $member_id, $year);
    $stmt_fetch_total_amount->execute();
    $stmt_fetch_total_amount->bind_result($total_contribution);
    if ($stmt_fetch_total_amount->fetch()) {
        $results['total_envelope_contribution'] = $total_contribution ?? 0.00;
    }
    $stmt_fetch_total_amount->close();

    // Fetch target amount for the specified year
    $stmt_fetch_target_amount = $conn->prepare("
        SELECT target AS target_amount
        FROM envelope_targets 
        WHERE member_id = ? AND YEAR(from_date) = ?
    ");
    if (!$stmt_fetch_target_amount) {
        error_log($conn->error);
        return $results;
    }

    $stmt_fetch_target_amount->bind_param("ii", $member_id, $year);
    $stmt_fetch_target_amount->execute();
    $stmt_fetch_target_amount->bind_result($results['yearly_envelope_target']);
    if (!$stmt_fetch_target_amount->fetch()) {
        $results['yearly_envelope_target'] = 0.00; 
    }
    $stmt_fetch_target_amount->close();

    // Fetch total number of distinct weeks with contributions in the specified year
    $stmt_fetch_total_envelopes = $conn->prepare("
        SELECT COUNT(DISTINCT CONCAT(YEAR(contribution_date), '-', WEEK(contribution_date, 1))) 
        FROM envelope_contribution
        WHERE member_id = ? 
          AND YEAR(contribution_date) = ? 
          AND contribution_date <= CURRENT_DATE();

    ");
    if (!$stmt_fetch_total_envelopes) {
        error_log($conn->error);
        return $results;
    }

    $stmt_fetch_total_envelopes->bind_param("ii", $member_id, $year);
    $stmt_fetch_total_envelopes->execute();
    $stmt_fetch_total_envelopes->bind_result($results['member_contributions_until_today']);
    if (!$stmt_fetch_total_envelopes->fetch()) {
        $results['member_contributions_until_today'] = 0; // Handle case where no contributions are found
    }
    $stmt_fetch_total_envelopes->close();

    // Fetch total number of Sundays in the specified year (up to today's date)
    $results['total_annual_envelopes'] = countSundays($year);

    // Fetch total Sundays up to today's date in the specified year
    $results['total_envelopes_until_today'] = countSundaysToDate($year);

    return $results;
}

function countSundays($year): int {
    $count = 0;
    $startDate = new DateTime("$year-01-01");
    $endDate = new DateTime("$year-12-31");

    // Loop through each date from start date to end date
    while ($startDate <= $endDate) {
        // Check if the current date is a Sunday
        if ($startDate->format('N') == 7) { // 7 represents Sunday
            $count++;
        }
        // Move to the next day
        $startDate->modify('+1 day');
    }

    return $count;
}

function countSundaysToDate($year): int {
    $count = 0;
    $startDate = new DateTime("$year-01-01");
    $endDate = new DateTime(); 

    // If the specified year is in the past, set end date to December 31 of that year
    if ($year < $endDate->format('Y')) {
        $endDate = new DateTime("$year-12-31");
    }

    // Loop through each date from start date to the end date
    while ($startDate <= $endDate) {
        // Check if the current date is a Sunday
        if ($startDate->format('N') == 7) { // 7 represents Sunday
            $count++;
        }
        // Move to the next day
        $startDate->modify('+1 day');
    }

    return $count;
}

class Number
{
    private $number;
    private $mamoja = ['', 'moja', 'mbili', 'tatu', 'nne', 'tano', 'sita', 'saba', 'nane', 'tisa'];
    private $makumi = ['', 'kumi', 'ishirini', 'thelathini', 'arobaini', 'hamsini', 'sitini', 'sabini', 'themanini', 'tisini'];
    private $mamia = ['', 'mia moja', 'mia mbili', 'mia tatu', 'mia nne', 'mia tano', 'mia sita', 'mia saba', 'mia nane', 'mia tisa'];
    private $cheo = ['', 'elfu', 'milioni', 'bilioni', 'trilioni', 'kuadrilioni', 'kuintilioni', 'seksitilioni', 'septilioni', 'oktilioni', 'nonilioni', 'desilioni', 'anidesilioni', 'dodesilioni', 'tradesilioni', 'kuatuordesilion', 'kuindesilioni', 'seksidesilioni', 'septendesilioni', 'oktodesilioni', 'novemdesilioni', 'vijintilioni'];

    public function __construct($number)
    {
        $this->number = $number;
        if (is_null($this->number)) {
            return '';
        } else {
            if (!is_numeric($this->number)) {
                throw new Exception("Kosa. Hujaingiza inteja.");
            }
        }
    }

    private function getOrder($number)
    {
        $order = 0;
        while ($number >= 1000) {
            $order++;
            $number = intdiv($number, 1000);
        }
        return $order;
    }

    private function getOrderRemainder($number)
    {
        $order = $this->getOrder($number);
        $remainder = $number % pow(10, 3 * $order);
        return $remainder;
    }

    private function convertToWordsHundreds($number)
    {
        $word = '';
        if ($number < 0) {
            $number = -$number;
            $word .= 'hasi ';
        }
        if ($number < 1000) {
            if ($number >= 100) {
                $hundred = intdiv($number, 100);
                $hundredr = $number % 100;
                if ($hundredr) {
                    $ten = intdiv($hundredr, 10);
                    $one = $hundredr % 10;
                    $word .= $this->mamia[$hundred];
                    if ($ten) {
                        $word .= ' na ' . $this->makumi[$ten];
                    }
                    if ($one) {
                        $word .= ' na ' . $this->mamoja[$one];
                    }
                } else {
                    $word .= $this->mamia[$hundred];
                }
            } elseif ($number >= 10) {
                $ten = intdiv($number, 10);
                $one = $number % 10;
                $word .= $this->makumi[$ten];
                if ($one) {
                    $word .= ' na ' . $this->mamoja[$one];
                }
            } elseif ($number < 10) {
                $word .= $this->mamoja[$number];
            }
        }
        return $word;
    }

    private function convertToWordsOrder($number)
    {
        $word = '';
        $order = $this->getOrder($number);
        $hundred = intdiv($number, pow(10, 3 * $order));
        if ($order == 1 && $hundred >= 100) {
            $laki = intdiv($hundred, 100);
            $lakir = $hundred % 100;
            $word .= 'laki ' . $this->mamoja[$laki];
            if ($lakir) {
                $word .= ' na elfu ' . $this->convertToWordsHundreds($lakir);
            }
            return $word;
        }
        return $this->cheo[$order] . ' ' . $this->convertToWordsHundreds($hundred);
    }

    private function convertToWordsOrderR($number)
    {
        $word = '';
        $order = $this->getOrder($number);
        $hundred = intdiv($number, pow(10, 3 * $order));
        if ($order == 1 && $hundred >= 100) {
            $laki = intdiv($hundred, 100);
            $lakir = $hundred % 100;
            $word .= 'laki ' . $this->mamoja[$laki];
            if ($lakir) {
                $word .= ' na ' . $this->convertToWordsHundreds($lakir) . ' elfu ';
            }
            return $word;
        }
        return $this->convertToWordsHundreds($hundred) . ' ' . $this->cheo[$order];
    }

    private function convertToDigits($snumber)
    {
        $word = '';
        $word_l = [];
        $digits = str_split($snumber);
        foreach ($digits as $i) {
            if (intval($i) == 0) {
                $word_l[] = 'sifuri';
            } else {
                $word_l[] = $this->mamoja[intval($i)];
            }
        }
        $word = implode(' ', $word_l);
        return $word;
    }

    private function getFractionDigits($integer = false)
    {
        $number_s = strval($this->number);
        try {
            $number_ls = preg_split('/[.]/', $number_s);
            $digits = $number_ls[1];
            if ($integer) {
                $digits = intval($digits);
            }
        } catch (Exception $e) {
            $digits = false;
        }
        return $digits;
    }

    public function convertToWords()
    {
        $word = '';
        $fraction = $this->getFractionDigits();
        try {
            $number_s = strval($this->number);
            $number_ls = preg_split('/[.]/', $number_s);
            $this->number = intval($number_ls[0]);
        } catch (Exception $e) {
            // Do nothing
        }
        if ($number_ls[0][0] == '-') {
            $this->number = -$this->number;
            $word .= 'hasi ';
        }
        if ($this->number == 0) {
            $word .= 'sifuri';
        }
        $number = $this->number;
        if ($number < 1000) {
            $word .= $this->convertToWordsHundreds($number);
        } else {
            if ($number % 1000) {
                $terminator = ' na ';
            } else {
                $terminator = '';
            }
            while ($number >= 1000) {
                $order = $this->getOrder($number);
                $digits_in_order = intdiv($number, pow(10, 3 * $order));
                $value_in_order = ($digits_in_order * pow(10, 3 * $order));
                $next_number = $number - $value_in_order;
                if ($number % 10000 < 100 && $number >= 10000 && $next_number < 100) {
                    $word .= $this->convertToWordsOrderR($number);
                } else {
                    $word .= $this->convertToWordsOrder($number);
                }
                $number = $next_number;
                if ($number) {
                    $word .= ',';
                }
                if ($order >= 1 && $number) {
                    $word .= ' ';
                }
            }
            if ($terminator) {
                $word = substr($word, 0, -2);
                $word .= $terminator;
                $word .= $this->convertToWordsHundreds($number);
            }
        }
        if ($fraction) {
            $word .= ' nukta ' . $this->convertToDigits($fraction);
        }
        return $word;
    }
}


function SystemInfoSMS($conn, $member) {
    $date = date('Y-m-d');

    // Get SMS credentials for the member
    $smsInfo = get_sms_credentials($conn, $member['head_parish_id']);

    // Check if credentials were retrieved successfully
    if (!$smsInfo) {
        error_log("No SMS credentials found for member ID: " . $member['member_id']);
        return false; // No credentials found
    }


    // Extract member information
    $first_name = isset($member['first_name']) ? $member['first_name'] : '[No First Name]';
    $phone = isset($member['phone']) ? $member['phone'] : '255678048567'; // Default phone number

    // Compose the new SMS message
    $message = "HITILAFU KWENYE MFUMO.\n";
    $message .= "Jana tarehe 08-11-'24, kulikuwa na tatizo katika mfumo wa Kanisa Langu lililopelekea kupokea ujumbe wa mchango wa Shs. 20,000/= kwa makosa kwenye akaunti yako. Tatizo limerekebishwa. Tunaomba radhi kwa usumbufu uliojitokeza.\n\n";
    $message .= "Kamati ya Mali na Fedha.\nKKKT-Usharika wa Elerai.";

    // Send SMS using quick send
    $smsClient = new SewmrSMSClient($smsInfo['api_token'], $smsInfo['sender_id']);
    $response = $smsClient->sendQuickSMS(null, $message, [$phone]);
    
    if (!isset($response['success']) || $response['success'] !== true) {
        $errorMessage = $response['message'] ?? "Unknown error";
        error_log("Failed to send SMS to {$phone}: $errorMessage");
        return false; // Return false if SMS failed
    }

    return true; 
}

function get_head_parish_name($conn, $head_parish_id) {
    $stmt = $conn->prepare("SELECT head_parish_name FROM head_parishes WHERE head_parish_id = ?");
    $stmt->bind_param("i", $head_parish_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        return $row['head_parish_name'];
    }

    return null;
}

function get_first_sub_parish_id($conn, $head_parish_id) {
    $stmt = $conn->prepare("SELECT sub_parish_id FROM sub_parishes WHERE head_parish_id = ? ORDER BY sub_parish_id ASC LIMIT 1");
    $stmt->bind_param("i", $head_parish_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        return $row['sub_parish_id'];
    }
    
    return null; // No sub parish found
}

function translateRoleToSwahili($role) {
    switch ($role) {
        case 'admin':
            return 'Msimamizi Mkuu';
        case 'pastor':
            return 'Mchungaji';
        case 'secretary':
            return 'Katibu';
        case 'chairperson':
            return 'Mwenyekiti';
        case 'clerk':
            return 'Karani';
        default:
            return 'Kiongozi';
    }
}


function sendAdminRegistrationSMS($conn, $head_parish_id, $admin) {
    $date = date('Y-m-d');

    // Get SMS credentials
    $smsInfo = get_sms_credentials($conn, $head_parish_id);
    if (!$smsInfo) {
        error_log("No SMS credentials found for sub parish ID: " . $sub_parish_id);
        return false;
    }

    // Get head parish name
    $head_parish_name = get_head_parish_name($conn, $head_parish_id);
    if (!$head_parish_name) {
        $head_parish_name = "Parokia"; 
    }

    // Extract admin details
    $first_name = ucwords(strtoupper($admin['first_name'])) ?? '[No First Name]';
    $phone = $admin['phone'] ?? '255678048567';
    $role_en = $admin['role'] ?? 'admin';
    $role = translateRoleToSwahili($role_en); 

    // Compose SMS message
    $message = "Shalom {$first_name},\n";
    $message .= "Umesajiliwa kama {$role} wa {$head_parish_name} katika mfumo wa Kanisa Langu.\n";
    $message .= "Tumia link hii kuingia: https://kanisalangu.sewmrtechnologies.com/head-parish/sign-in\n\n";
    $message .= "Karibu,\n{$head_parish_name}.";

    // Send SMS using quick send
    $smsClient = new SewmrSMSClient($smsInfo['api_token'], $smsInfo['sender_id']);
    $response = $smsClient->sendQuickSMS(null, $message, [$phone]);
    
    if (!isset($response['success']) || $response['success'] !== true) {
        $errorMessage = $response['message'] ?? "Unknown error";
        error_log("Failed to send SMS to {$phone}: $errorMessage");
        return false;
    }

    return true;
}


function getExpenseGroupDetails($conn, $head_parish_id, $account_id, $year, $target, $sub_parish_id = null, $community_id = null, $group_id = null) {
    // Define the tables based on the target type
    switch ($target) {
        case 'head-parish':
            $expense_group_table = "head_parish_expense_groups";
            $expense_name_table = "head_parish_expense_names";
            $expense_budget_table = "head_parish_expense_budgets";
            $expense_group_column = "head_parish_id";
            break;
        case 'sub-parish':
            $expense_group_table = "sub_parish_expense_groups";
            $expense_name_table = "sub_parish_expense_names";
            $expense_budget_table = "sub_parish_expense_budgets";
            $expense_group_column = "sub_parish_id";
            break;
        case 'community':
            $expense_group_table = "community_expense_groups";
            $expense_name_table = "community_expense_names";
            $expense_budget_table = "community_expense_budgets";
            $expense_group_column = "community_id";
            break;
        case 'group':
        case 'groups':
            $expense_group_table = "group_expense_groups";
            $expense_name_table = "group_expense_names";
            $expense_budget_table = "group_expense_budgets";
            $expense_group_column = "group_id";
            break;
        default:
            return []; // Invalid target
    }
    // Build the base query for fetching expense groups and their associated budgets
    $query = "
        SELECT 
            eg.expense_group_name,
            eg.expense_group_identifier,
            eg.account_id,
            en.expense_name,
            eb.budgeted_amount,
            eb.start_date,
            eb.end_date,
            eb.budget_description,
            en.expense_name_id,
            eb.budget_id
        FROM $expense_group_table eg
        JOIN $expense_name_table en ON eg.expense_group_id = en.expense_group_id
        JOIN $expense_budget_table eb ON en.expense_name_id = eb.expense_name_id
        WHERE eg.$expense_group_column = ? AND eg.account_id = ?
        AND (YEAR(eb.start_date) = ? OR YEAR(eb.end_date) = ?)";  // Extract year from start_date and end_date
    
    // Add conditions based on the target and provided IDs
    if ($target == 'sub-parish' && $sub_parish_id) {
        $query .= " AND eg.sub_parish_id = ?";
    }
    if ($target == 'community' && $sub_parish_id && $community_id) {
        $query .= " AND eg.sub_parish_id = ? AND eg.community_id = ?";
    }
    if ($target == 'group' && $group_id) {
        $query .= " AND eg.group_id = ?";
    }
    
    // Prepare the statement based on conditions
    $stmt = $conn->prepare($query);
    
    // Bind the parameters based on the target
    if ($target == 'sub-parish' && $sub_parish_id) {
        // Binding parameters for 'sub-parish' target: head_parish_id, year (for both start_date and end_date), sub_parish_id
        $stmt->bind_param("iissi", $head_parish_id, $account_id, $year, $year, $sub_parish_id);
    } elseif ($target == 'community' && $sub_parish_id && $community_id) {
        // Binding parameters for 'community' target: head_parish_id, year (for both start_date and end_date), sub_parish_id, community_id
        $stmt->bind_param("iissii", $head_parish_id, $account_id, $year, $year, $sub_parish_id, $community_id);
    } elseif ($target == 'group' && $group_id) {
        // Binding parameters for 'group' target: head_parish_id, year (for both start_date and end_date), group_id
        $stmt->bind_param("iiisi", $head_parish_id, $account_id, $year, $year, $group_id);
    } else {
        // Binding parameters for the default case: head_parish_id and year (for both start_date and end_date)
        $stmt->bind_param("iiss", $head_parish_id, $account_id, $year, $year);
    }

    // Execute query
    $stmt->execute();
    $result = $stmt->get_result();

    // Initialize an array to store the response data
    $expense_groups = [];

    while ($row = $result->fetch_assoc()) {
        // Generate the expense identifier (example: A001, A002, etc.)
        $expense_identifier = $row['expense_group_identifier'] . str_pad($row['expense_name_id'], 2, '0', STR_PAD_LEFT);
    
        // Check if the expense group already exists in the array
        $found = false;
        foreach ($expense_groups as &$group) {
            if ($group['expense_group'] == $row['expense_group_name'] && $group['expense_code'] == $row['expense_group_identifier']) {
                // If the group exists, append the new budget to the existing expense group
                $group['expense_budgets'][] = [
                    'expense_id' => $row['expense_name_id'],
                    'expense_name' => $row['expense_name'],
                    'expense_identifier' => $expense_identifier,
                    'expense_budget' => $row['budgeted_amount']
                ];
                $found = true;
                break;
            }
        }
    
        // If the group does not exist, create a new group
        if (!$found) {
            $expense_groups[] = [
                'expense_group' => $row['expense_group_name'],
                'expense_code' => $row['expense_group_identifier'],
                'expense_budgets' => [
                    [
                        'expense_id' => $row['expense_name_id'],
                        'expense_name' => $row['expense_name'],
                        'expense_identifier' => $expense_identifier,
                        'expense_budget' => $row['budgeted_amount']
                    ]
                ]
            ];
        }
    }


    // Return the data or false if no results were found
    return count($expense_groups) > 0 ? $expense_groups : [];
}


function getHeadParishAdminDetailsById($conn, $head_parish_id, $admin_id) {
    $sql = "SELECT 
                head_parish_admin_fullname, 
                head_parish_admin_phone, 
                head_parish_admin_email, 
                head_parish_admin_role 
            FROM head_parish_admins 
            WHERE head_parish_id = ? AND head_parish_admin_id = ? 
            LIMIT 1";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $head_parish_id, $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        $fullname = ucwords(strtolower($row['head_parish_admin_fullname']));
        $phone = $row['head_parish_admin_phone'];
        $email = strtolower($row['head_parish_admin_email']);
        $role_en = strtolower($row['head_parish_admin_role']);
        $role_sw = translateRoleToSwahili($role_en);

        return [
            'full_name' => $fullname,
            'phone' => $phone,
            'email' => $email,
            'role_en' => $role_en,
            'role_sw' => $role_sw
        ];
    }

    return null;
}

function getMemberIdsRecordedByAdmin($conn, $harambee_id, $contribution_date, $target, $admin_id) {
    // Determine the correct contributions table based on the target
    switch ($target) {
        case 'head-parish':
            $table = "head_parish_harambee_contribution";
            break;
        case 'sub-parish':
            $table = "sub_parish_harambee_contribution";
            break;
        case 'community':
            $table = "community_harambee_contribution";
            break;
        case 'group':
        case 'groups':
            $table = "groups_harambee_contribution";
            break;
        default:
            return [];  // Return an empty array if target is invalid
    }

    // Prepare SQL to get unique member IDs
    $query = "
        SELECT DISTINCT member_id 
        FROM $table 
        WHERE harambee_id = ? 
          AND contribution_date = ? 
          AND recorded_by = ?
    ";

    $stmt = $conn->prepare($query);
    if (!$stmt) {
        return [];  // Return an empty array in case of an error preparing the query
    }

    $stmt->bind_param("isi", $harambee_id, $contribution_date, $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $memberIds = [];
    while ($row = $result->fetch_assoc()) {
        $memberIds[] = (int)$row['member_id'];
    }

    $stmt->close();
    return $memberIds;  // Return the array of member IDs (or empty array if no results)
}


function getApprovedExpenseByQuarter($conn, $head_parish_id, $sub_parish_id = null, $community_id = null, $group_id = null, $year, $expense_name_id, $target) {
    // Define the tables based on the target type
    switch ($target) {
        case 'head-parish':
            $table = "head_parish_expense_requests";
            $id_column = "head_parish_id";
            break;
        case 'sub-parish':
            $table = "sub_parish_expense_requests";
            $id_column = "sub_parish_id";
            break;
        case 'community':
            $table = "community_expense_requests";
            $id_column = "community_id";
            break;
        case 'group':
        case 'groups':
            $table = "group_expense_requests";
            $id_column = "group_id";
            break;
        default:
            return []; // Invalid target
    }

    // Define the quarters
    $quarters = [
        'Q1' => ['start' => "$year-01-01", 'end' => "$year-03-31"],
        'Q2' => ['start' => "$year-04-01", 'end' => "$year-06-30"],
        'Q3' => ['start' => "$year-07-01", 'end' => "$year-09-30"],
        'Q4' => ['start' => "$year-10-01", 'end' => "$year-12-31"]
    ];

    // Initialize an array to store the total approved amounts for each quarter
    $quarter_totals = [
        'Q1' => 0,
        'Q2' => 0,
        'Q3' => 0,
        'Q4' => 0
    ];

    // Build the query
    $query = "
        SELECT request_amount, request_datetime
        FROM $table
        WHERE expense_name_id = ? AND request_status = 'Approved'
        AND request_datetime BETWEEN ? AND ?";
    
    // Prepare the statement
    $stmt = $conn->prepare($query);

    // Loop through the quarters and calculate the total for each
    foreach ($quarters as $quarter => $dates) {
        // Adjust the query based on the target
        if ($target == 'head-parish') {
            $stmt->bind_param("iss", $expense_name_id, $dates['start'], $dates['end']);
        } elseif ($target == 'sub-parish' && $sub_parish_id) {
            $stmt->bind_param("iiss", $head_parish_id, $expense_name_id, $dates['start'], $dates['end']);
        } elseif ($target == 'community' && $sub_parish_id && $community_id) {
            $stmt->bind_param("iissi", $head_parish_id, $expense_name_id, $dates['start'], $dates['end'], $sub_parish_id, $community_id);
        } elseif ($target == 'group' && $group_id) {
            $stmt->bind_param("iiiss", $head_parish_id, $expense_name_id, $dates['start'], $dates['end'], $group_id);
        } else {
            $stmt->bind_param("iss", $expense_name_id, $dates['start'], $dates['end']);
        }

        // Execute the statement
        $stmt->execute();
        $result = $stmt->get_result();
        
        // Sum the approved amounts for the current quarter
        $total_amount = 0;
        while ($row = $result->fetch_assoc()) {
            $total_amount += $row['request_amount'];
        }

        // Store the total in the corresponding quarter
        $quarter_totals[$quarter] = $total_amount;
    }

    // Return the total amounts for each quarter
    return $quarter_totals;
}


function getBankAccountRevenueDetails($conn, $head_parish_id, $year, $target, $sub_parish_id = null, $community_id = null, $group_id = null) {
    // Define the tables and the revenue query based on the target type
    switch ($target) {
        case 'head-parish':
            $expense_budget_table = "head_parish_annual_expense_budget";
            $revenue_query = "
                SELECT account_id, SUM(revenue_amount) AS revenue_amount
                FROM (
                    -- Head parish revenues
                    SELECT rs.account_id, r.revenue_amount
                    FROM head_parish_revenues r
                    INNER JOIN head_parish_revenue_streams rs ON r.revenue_stream_id = rs.revenue_stream_id
                    WHERE YEAR(r.revenue_date) = ? AND r.head_parish_id = ?
        
                    UNION ALL
        
                    -- Other head parish revenues
                    SELECT rs.account_id, r.revenue_amount
                    FROM other_head_parish_revenues r
                    INNER JOIN head_parish_revenue_streams rs ON r.revenue_stream_id = rs.revenue_stream_id
                    WHERE YEAR(r.revenue_date) = ? AND r.head_parish_id = ?
        
                    UNION ALL
        
                    -- Sub-parish revenues under this head parish
                    SELECT rs.account_id, r.revenue_amount
                    FROM sub_parish_revenues r
                    INNER JOIN head_parish_revenue_streams rs ON r.revenue_stream_id = rs.revenue_stream_id
                    WHERE YEAR(r.revenue_date) = ? AND r.head_parish_id = ?
        
                    UNION ALL
        
                    -- Community revenues under this head parish
                    SELECT rs.account_id, r.revenue_amount
                    FROM community_revenues r
                    INNER JOIN head_parish_revenue_streams rs ON r.revenue_stream_id = rs.revenue_stream_id
                    WHERE YEAR(r.revenue_date) = ? AND r.head_parish_id = ?
        
                    UNION ALL
        
                    -- Group revenues under this head parish
                    SELECT rs.account_id, r.revenue_amount
                    FROM group_revenues r
                    INNER JOIN head_parish_revenue_streams rs ON r.revenue_stream_id = rs.revenue_stream_id
                    WHERE YEAR(r.revenue_date) = ? AND r.head_parish_id = ?
                ) combined
                GROUP BY account_id
            ";
            break;

        case 'sub-parish':
            $expense_budget_table = "sub_parish_annual_expense_budget";
            $revenue_query = "
                SELECT rs.account_id, SUM(r.revenue_amount) AS revenue_amount
                FROM sub_parish_revenues r
                INNER JOIN head_parish_revenue_streams rs ON r.revenue_stream_id = rs.revenue_stream_id
                WHERE YEAR(r.revenue_date) = ? AND r.sub_parish_id = ?
                GROUP BY rs.account_id";
            break;
        case 'community':
            $expense_budget_table = "community_annual_expense_budget";
            $revenue_query = "
                SELECT rs.account_id, SUM(r.revenue_amount) AS revenue_amount
                FROM community_revenues r
                INNER JOIN head_parish_revenue_streams rs ON r.revenue_stream_id = rs.revenue_stream_id
                WHERE YEAR(r.revenue_date) = ?  AND r.sub_parish_id = ? AND  AND r.community_id = ?
                GROUP BY rs.account_id";
            break;
        case 'group':
        case 'groups':
            $expense_budget_table = "group_annual_expense_budget";
            $revenue_query = "
                SELECT rs.account_id, SUM(r.revenue_amount) AS revenue_amount
                FROM group_revenues r
                INNER JOIN head_parish_revenue_streams rs ON r.revenue_stream_id = rs.revenue_stream_id
                WHERE YEAR(r.revenue_date) = ?  AND r.group_id = ?
                GROUP BY rs.account_id";
            break;
        default:
            return []; // Invalid target
    }

    // Fetch revenue data
    $stmt = $conn->prepare($revenue_query);
    if ($target == 'head-parish') {
       $stmt->bind_param("iiiiiiiiii", $year, $head_parish_id, $year, $head_parish_id, $year, $head_parish_id, $year, $head_parish_id, $year, $head_parish_id);
    } elseif ($target == 'sub-parish') {
        $stmt->bind_param("ii", $year, $sub_parish_id);
    } elseif ($target == 'community') {
        $stmt->bind_param("iii", $year, $sub_parish_id, $community_id);
    } elseif ($target == 'group') {
        $stmt->bind_param("di", $year, $group_id);
    }
    $stmt->execute();
    
    $revenue_result = $stmt->get_result();

    // Store revenue data by account_id
    $revenue_data = [];
    while ($row = $revenue_result->fetch_assoc()) {
        $revenue_data[$row['account_id']] = $row['revenue_amount'];
    }

    // Fetch bank account details and revenue targets
    $query = "
        SELECT 
            ba.account_id,
            ba.account_name,
            ba.balance AS account_balance,
            COALESCE(SUM(rt.expense_budget_target_amount), 0) AS annual_revenue_target
        FROM head_parish_bank_accounts ba
        LEFT JOIN $expense_budget_table rt ON ba.account_id = rt.bank_account_id AND YEAR(rt.expense_budget_target_start_date) = ?
        WHERE ba.head_parish_id = ?
    ";


    $query .= " GROUP BY ba.account_id";

    // Prepare the statement for bank account details and revenue targets
    $stmt = $conn->prepare($query);

    $stmt->bind_param("ii", $year, $head_parish_id);

    // Execute query
    $stmt->execute();
    $result = $stmt->get_result();

    // Initialize an array to store the bank account details and merge data
    $bank_accounts = [];

    while ($row = $result->fetch_assoc()) {
        $account_id = $row['account_id'];
    
        // Get the account name and strip the left portion before the first hyphen
        $account_name = $row['account_name'];
        $account_balance = $row['account_balance'];
        if (strpos($account_name, '-') !== false) {
            // If a hyphen exists, get the part after the first hyphen
            $account_name = trim(explode('-', $account_name, 2)[1]);
        }
    
        // Combine the revenue and target data
        $bank_accounts[] = [
            'account_id' => $account_id,
            'account_name' => $account_name,
            'account_balance' => $account_balance,
            'annual_revenue_target' => $row['annual_revenue_target'],
            'annual_revenue' => isset($revenue_data[$account_id]) ? $revenue_data[$account_id] : 0,
            'balance' => $row['annual_revenue_target'] - (isset($revenue_data[$account_id]) ? $revenue_data[$account_id] : 0) // Calculating balance as difference
        ];
    }


    // Return the data or an empty array if no results were found
    return count($bank_accounts) > 0 ? $bank_accounts : [];
}


function getBankAccountName($conn, $account_id) {
    // Sanitize the account_id to prevent SQL injection
    $account_id = intval($account_id);

    // SQL query to get the account name based on account_id
    $query = "SELECT account_name FROM head_parish_bank_accounts WHERE account_id = ?";
    
    // Prepare the SQL statement
    if ($stmt = $conn->prepare($query)) {
        // Bind the account_id parameter
        $stmt->bind_param("i", $account_id);

        // Execute the statement
        $stmt->execute();

        // Bind the result to a variable
        $stmt->bind_result($account_name);

        // Fetch the result
        if ($stmt->fetch()) {
            if (strpos($account_name, '-') !== false) {
                // If a hyphen exists, get the part after the first hyphen
                $account_name = trim(explode('-', $account_name, 2)[1]);
            }
            // Return the account name
            return $account_name;
        } else {
            // Return null if the account ID is not found
            return null;
        }

        // Close the statement
        $stmt->close();
    } else {
        // Return false if the query could not be prepared
        return false;
    }
}

function getOperationalLevelName($conn, $head_parish_id, $sub_parish_id = null, $community_id = null, $group_id = null, $target) {
    // Initialize query and bind parameters based on target
    switch ($target) {
        case 'head-parish':
            $query = "SELECT head_parish_name FROM head_parishes WHERE head_parish_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $head_parish_id);
            break;
        
        case 'sub-parish':
            $query = "SELECT sub_parish_name FROM sub_parishes WHERE sub_parish_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $sub_parish_id);
            break;

        case 'community':
            $query = "SELECT community_name FROM communities WHERE community_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $community_id);
            break;

        case 'group':
        case 'groups':
            // For group, return the community name based on community_id
            $query = "SELECT group_name FROM groups WHERE group_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $group_id);
            break;

        default:
            return null; // Invalid target
    }

    // Execute query and fetch the result
    $stmt->execute();
    $stmt->bind_result($target_name);
    $stmt->fetch();
    
    // Return the target name or null if not found
    return $target_name ? $target_name : null;
}


function sendChurchMemberOTP($conn, $member, $otp, $request_type) {
    // Get SMS credentials for the member
    $smsInfo = get_sms_credentials($conn, $member['head_parish_id']); 

    // Check if credentials were retrieved successfully
    if (!$smsInfo) {
        error_log("No SMS credentials found");
        return false; 
    }

    // Decrypted API token and sender ID
    $apiToken  = $smsInfo['api_token'];
    $senderId  = $smsInfo['sender_id'];

    // Customize the message based on the request type
    $message = '';
    switch ($request_type) {
        case 'registration':
            $message = "Shalom " . ucfirst(strtolower($member['first_name'])) . "! To complete your registration, use the code $otp. This code expires soon. Do not share it with anyone.";
            break;
        case 'reset':
            $message = "We've received a request to reset your password. Use $otp to proceed. If you didn't request this, ignore this message.";
            break;
        default:
            error_log("Invalid request type: $request_type");
            return false; 
    }

    $phone = $member['phone'];

    // Initialize the new SMS client
    $smsClient = new SewmrSMSClient($apiToken, $senderId);

    // Send the SMS
    $response = $smsClient->sendQuickSMS(null, $message, [$phone]);
    
    // Handle the response
    if (!isset($response['success']) || $response['success'] !== true) {
        $errorMessage = $response['message'] ?? "Unknown error";
        error_log("Failed to send SMS to {$phone}: $errorMessage");
        return false; 
    }

    return true; 
}


function sendChurchMemberRegistrationMessage($conn, $member) {
    // Get SMS credentials for the member
    $smsInfo = get_sms_credentials($conn, $member['head_parish_id']); 

    // Check if credentials were retrieved successfully
    if (!$smsInfo) {
        error_log("No SMS credentials found");
        return false; // No credentials found
    }

    $phone = $member['phone'];
    $envelope_number = $member['envelope_number'];
    
    // Convert first name to lowercase and capitalize each word
    $first_name = ucwords(strtolower($member['first_name']));

    // Get the head parish secretary's phone number
    $secretary_phone = getHeadParishSecretaryPhone($conn);
    $secretary_info = $secretary_phone ? "\nKatibu\n{$secretary_phone}" : "";

    // Compose the SMS message with the OTP
    if (empty($envelope_number)) {
        $message = "Shalom {$first_name}!\nUmesajiliwa kwenye Mfumo wetu wa Kanisa.\nMungu akubariki.{$secretary_info}";
    } else {
        $message = "Shalom {$first_name}!\nUmesajiliwa kwenye Mfumo wetu wa Kanisa, kwa Bahasha Na. {$envelope_number}\nMungu akubariki.{$secretary_info}";
    }

    // Send SMS using quick send
    $smsClient = new SewmrSMSClient($smsInfo['api_token'], $smsInfo['sender_id']);
    $response = $smsClient->sendQuickSMS(null, $message, [$phone]);
    
    if (!isset($response['success']) || $response['success'] !== true) {
        $errorMessage = $response['message'] ?? "Unknown error";
        error_log("Failed to send SMS to {$phone}: $errorMessage");
        return false; // Return false if SMS failed
    }


    return true; 
}

function sendChurchMemberEnvelopeUpdateMessage($conn, $member, $old_envelope_number, $new_envelope_number = null) {
    // Get SMS credentials for the member
    $smsInfo = get_sms_credentials($conn, $member['head_parish_id']); 

    // Check if credentials were retrieved successfully
    if (!$smsInfo) {
        error_log("No SMS credentials found");
        return false; // No credentials found
    }


    $phone = $member['phone'];

    // Convert first name to lowercase and capitalize each word
    $first_name = ucwords(strtolower($member['first_name']));

    // Get the head parish secretary's phone number
    $secretary_phone = getHeadParishSecretaryPhone($conn);
    $secretary_info = $secretary_phone ? "\nKatibu\n{$secretary_phone}" : "";

    // Compose the SMS message based on whether the envelope number has changed
    if ($new_envelope_number === null || $new_envelope_number === $old_envelope_number) {
        // No change in envelope number
        $message = "Shalom {$first_name}!\nTaarifa zako zimefanyiwa marekebisho.\nMungu akubariki.{$secretary_info}";
    } else {
        // Envelope number has changed
        $message = "Shalom {$first_name}!\nNamba yako ya bahasha imebadilishwa kutoka {$old_envelope_number} kwenda {$new_envelope_number}.\nMungu akubariki.{$secretary_info}";
    }

   // Send SMS using quick send
    $smsClient = new SewmrSMSClient($smsInfo['api_token'], $smsInfo['sender_id']);
    $response = $smsClient->sendQuickSMS(null, $message, [$phone]);
    
    if (!isset($response['success']) || $response['success'] !== true) {
        $errorMessage = $response['message'] ?? "Unknown error";
        error_log("Failed to send SMS to {$phone}: $errorMessage");
        return false; // Return false if SMS failed
    }


    return true; // SMS sent successfully
}


function getMemberHarambee($conn, $member_id, $target) {
    // Define the table mappings based on the target
    $tables = getTablesByTarget($target);

    if (!$tables) {
        return false; // Invalid target type
    }

    list($target_table, $group_info_table, $harambee_table, $contribution_table) = $tables;
    
    $harambee_ids = [];
        
    // Retrieve member details (head_parish_id, sub_parish_id, community_id)
    $member_details = getMemberDetails($conn, $member_id);
    if ($member_details->num_rows == 0) {
        return false; // Member not found
    }

    $member = $member_details->fetch_assoc();
    $head_parish_id = $member['head_parish_id'];
    $sub_parish_id = $member['sub_parish_id'];
    $community_id = $member['community_id'];
    
    
     // New logic to get Harambee IDs based on the target (head parish, sub parish, community)
    switch ($target) {
        case 'head-parish':
            $query = "SELECT DISTINCT harambee_id FROM $harambee_table WHERE head_parish_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $head_parish_id);
            break;

        case 'sub-parish':
            $query = "SELECT DISTINCT harambee_id FROM $harambee_table WHERE sub_parish_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $sub_parish_id);
            break;

        case 'community':
            $query = "SELECT DISTINCT harambee_id FROM $harambee_table WHERE community_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $community_id);
            break;

        case 'groups':
            $query = "SELECT DISTINCT harambee_id FROM $harambee_table WHERE head_parish_id = ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $head_parish_id);
            break;
            
        default:
            return false; // Invalid target type
    }
    
    // Execute the query to get the Harambee IDs based on target
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        if ($row['harambee_id'] !== null) {
            $harambee_ids[] = $row['harambee_id'];
        }
    }

    // Query the target table for Harambee IDs
    $query = "SELECT DISTINCT harambee_id FROM $target_table WHERE member_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        if ($row['harambee_id'] !== null) {
            $harambee_ids[] = $row['harambee_id'];
        }
    }

    // Query the contribution table for Harambee IDs
    $query = "SELECT DISTINCT harambee_id FROM $contribution_table WHERE member_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $member_id);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        if ($row['harambee_id'] !== null) {
            $harambee_ids[] = $row['harambee_id'];
        }
    }
    
    // Return unique Harambee IDs in descending order
    $unique_ids = array_unique($harambee_ids);
    rsort($unique_ids); // Sort in descending order by harambee_id
    return $unique_ids;
}


function getHarambeeDetailsByTarget($conn, $harambee_id, $target) {
    // Define the table mappings based on the target
    $tables = getTablesByTarget($target);

    if (!$tables) {
        return false; // Invalid target type
    }

    list($target_table, $group_info_table, $harambee_table, $contribution_table) = $tables;

    // Prepare the query to fetch the Harambee details
    $query = "SELECT harambee_id, name, description, from_date, to_date, amount 
              FROM $harambee_table 
              WHERE harambee_id = ?";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $harambee_id);
    $stmt->execute();
    $result = $stmt->get_result();

    // Fetch the result
    if ($row = $result->fetch_assoc()) {
        return $row;
    } else {
        return false; // No Harambee found with the given ID
    }
}


// Function to get the member's current Harambee target
function getMemberHarambeeTarget($conn, $member_id, $target, $harambee_id) {
    // Get the appropriate tables for the target
    $tables = getTablesByTarget($target);

    if (!$tables) {
        return 0; // Invalid target type
    }

    list($target_table, $group_info_table, $harambee_table, $contribution_table) = $tables;

    // Query the target table to fetch the member's target for the specific Harambee ID
    $query = "SELECT target as target_amount FROM $target_table WHERE member_id = ? AND harambee_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $member_id, $harambee_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        // Return the target amount if found
        $row = $result->fetch_assoc();
        return $row['target_amount'];
    } else {
        // If no target is found, return 0
        return 0;
    }
}

// Function to insert or update the member's Harambee target and additional details
function setMemberHarambeeTarget($conn, $member_id, $target, $harambee_id, $target_amount, $target_type = 'individual') {
    // Get the appropriate tables for the target
    $tables = getTablesByTarget($target);

    if (!$tables) {
        return false; // Invalid target type
    }

    list($target_table, $group_info_table, $harambee_table, $contribution_table) = $tables;

    // Retrieve member details (head_parish_id, sub_parish_id, community_id)
    $member_details = getMemberDetails($conn, $member_id);
    if ($member_details->num_rows == 0) {
        return false; // Member not found
    }

    $member = $member_details->fetch_assoc();
    $head_parish_id = $member['head_parish_id'];
    $sub_parish_id = $member['sub_parish_id'];
    $community_id = $member['community_id'];


    $harambee_committee_responsibility = null;

    // Check if the target already exists for this member and harambee
    $query = "SELECT COUNT(*) as count FROM $target_table WHERE member_id = ? AND harambee_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $member_id, $harambee_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    if ($row['count'] > 0) {
        // If target exists, update it
        $update_query = "UPDATE $target_table 
                         SET target = ?, target_type = ?, 
                             head_parish_id = ?, sub_parish_id = ?, community_id = ?
                         WHERE member_id = ? AND harambee_id = ?";
        $update_stmt = $conn->prepare($update_query);
        $update_stmt->bind_param("dsiiiii", $target_amount, $target_type, 
                                 $head_parish_id, $sub_parish_id, $community_id, $member_id, $harambee_id);
        $update_stmt->execute();
        return $update_stmt->affected_rows > 0; // Return true if update was successful
    } else {
        // If no target exists, insert a new record
        $insert_query = "INSERT INTO $target_table (member_id, harambee_id, target, target_type, head_parish_id, sub_parish_id, community_id) 
                          VALUES (?, ?, ?, ?, ?, ?, ?)";
        $insert_stmt = $conn->prepare($insert_query);
        $insert_stmt->bind_param("iidsiii", $member_id, $harambee_id, $target_amount, $target_type, $head_parish_id, $sub_parish_id, $community_id);
        $insert_stmt->execute();
        return $insert_stmt->affected_rows > 0; // Return true if insert was successful
    }
}

// Function to get the member's current Harambee target type
function getMemberHarambeeTargetType($conn, $member_id, $target, $harambee_id) {
    // Get the appropriate tables for the target
    $tables = getTablesByTarget($target);

    if (!$tables) {
        return null; // Invalid target type
    }

    // Extract the tables from the array
    list($target_table, $group_info_table, $harambee_table, $contribution_table) = $tables;

    // Query the target table to fetch the member's target type for the specific Harambee ID
    $query = "SELECT target_type FROM $target_table WHERE member_id = ? AND harambee_id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $member_id, $harambee_id); // Bind the member_id and harambee_id
    $stmt->execute();
    $result = $stmt->get_result();

    // Check if a target type is found
    if ($result->num_rows > 0) {
        // Fetch the target type and return it
        $row = $result->fetch_assoc();
        return $row['target_type']; // Return the target type (group or individual)
    } else {
        // If no target type is found, return null
        return null;
    }
}


class SmsSender {
    // Define constants for the API URLs
    const API_URL = "https://sewmr-sms.sewmr.com/api/messaging/send-sms/"; // SMS Sending URL
    const TOKEN_URL = "https://sewmr-sms.sewmr.com/api/auth/generate-token/"; // Token Generation URL

    private $username;
    private $password;
    private $senderId;
    private $accessToken;

    // Constructor to initialize credentials and access token
    public function __construct($username, $password, $senderId) {
        $this->username = $username;
        $this->password = $password;
        $this->senderId = $senderId;
        $this->accessToken = $this->generateAccessToken();
    }

    // Getter for accessToken (to access private property)
    public function getAccessToken() {
        return $this->accessToken;
    }

    // Method to generate the access token
    private function generateAccessToken() {
        // Prepare the authorization header for Basic Auth
        $credentials = base64_encode($this->username . ":" . $this->password);

        // Initialize cURL to request access token
        $ch = curl_init(self::TOKEN_URL);

        // Set cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Authorization: Basic " . $credentials,
            "Content-Type: application/json"
        ]);

        // Execute the cURL request and get the response
        $response = curl_exec($ch);

        // Check for cURL errors
        if ($response === false) {
            curl_close($ch);
            return json_encode([
                "success" => false,
                "message" => "cURL Error: " . curl_error($ch)
            ]);
        }

        // Decode the JSON response
        $responseData = json_decode($response, true);

        // Close the cURL session
        curl_close($ch);

        // Check if token generation was successful
        if (isset($responseData['success']) && $responseData['success'] == true) {
            return $responseData['access_token']; // Return the access token
        } else {
            return json_encode([
                "success" => false,
                "message" => "Failed to generate token: " . ($responseData['message'] ?? "Unknown error")
            ]);
        }
    }

    // Method to send SMS using the generated access token
    public function sendSms($destination, $message) {
        if (!$this->getAccessToken()) {
            return json_encode([
                "success" => false,
                "message" => "Access token is missing or invalid."
            ]);
        }

        // Prepare the POST data
        $data = [
            "access_token" => $this->getAccessToken(),
            "source" => $this->senderId,
            "destination" => $destination,
            "message" => $message
        ];

        // Convert the data array to JSON format
        $jsonData = json_encode($data);

        // Initialize cURL to send SMS
        $ch = curl_init(self::API_URL);

        // Set cURL options
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $jsonData);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: Bearer " . $this->getAccessToken()
        ]);

        // Execute the cURL request and get the response
        $response = curl_exec($ch);

        error_log("Raw SMS API Response: " . $response);
        
        // Check for cURL errors
        if ($response === false) {
            curl_close($ch);
            return json_encode([
                "success" => false,
                "message" => "cURL Error: " . curl_error($ch)
            ]);
        }

        // Decode the JSON response from the API
        $responseData = json_decode($response, true);
        
        // Log the decoded response (convert array to string for logging)
        // error_log("Decoded response: " . print_r($responseData, true));
        // Close the cURL session
        curl_close($ch);

        // Check if the SMS was sent successfully
        if (isset($responseData['success']) && $responseData['success'] == true) {
            return json_encode([
                "success" => true,
                "message" => "SMS sent successfully!"
            ]);
        } else {
            return json_encode([
                "success" => false,
                "message" => "Failed to send SMS: " . ($responseData['message'] ?? "Unknown error")
            ]);
        }
    }
}



class SmsSenderLegacy {
    // Define the API URL
    const API_URL = "https://messaging-service.co.tz/api/sms/v1/text/single";

    private $username;
    private $password;
    private $senderId;

    // Constructor to initialize credentials
    public function __construct($username, $password, $senderId) {
        $this->username = $username;
        $this->password = $password;
        $this->senderId = $senderId;
    }

    // Method to send SMS using Basic Auth with base64 encoded credentials
    public function sendSms($destination, $message) {
        $credentials = base64_encode($this->username . ":" . $this->password);

        // Prepare the data payload
        $data = [
            'from' => $this->senderId,
            'to' => $destination,
            'text' => $message
        ];

        // Initialize cURL to send SMS
        $ch = curl_init();

        curl_setopt_array($ch, [
            CURLOPT_URL => self::API_URL,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'POST',
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Authorization: Basic ' . $credentials,
                'Content-Type: application/json',
                'Accept: application/json',
            ],
        ]);

        // Execute the cURL request and get the response
        $response = curl_exec($ch);
        
        // Log the raw response for debugging
        // error_log("SMS API raw response: " . $response);

        // Check for cURL errors
        if ($response === false) {
            curl_close($ch);
            return json_encode([
                "success" => false,
                "message" => "cURL Error: " . curl_error($ch)
            ]);
        }

        // Decode the JSON response from the API
        $responseData = json_decode($response, true);

        // Close the cURL session
        curl_close($ch);

        // Extract status details
        $status = $responseData['messages'][0]['status'] ?? [];
        $recipient = $responseData['messages'][0]['to'] ?? 'Unknown';
        
        // Log the full response for transparency/debugging
        // error_log("SMS API raw response: " . json_encode($responseData));
        
        // Determine if the message was accepted for delivery
        if (isset($status['groupName']) && $status['groupName'] === 'PENDING' && $status['name'] === 'PENDING_ENROUTE') {
            return json_encode([
                "success" => true,
                "message" => "SMS sent successfully to $recipient!"
            ]);
        } else {
            $statusDescription = $status['description'] ?? 'Unknown error';
            // error_log("Failed to send SMS to $recipient: $statusDescription");
        
            return json_encode([
                "success" => false,
                "message" => "Failed to send SMS: " . $statusDescription
            ]);
        }
    }
}


class SewmrSMSClient {
    private $baseUrl;
    private $accessToken;
    private $headers;
    private $defaultSenderId;

    public function __construct(
        $accessToken = null, 
        $defaultSenderId = null
    ) { 
        // API base URL
        $this->baseUrl = "https://api.sewmrsms.co.tz/api/v1/";
        $this->accessToken = $accessToken;
        $this->defaultSenderId = $defaultSenderId;

        // Headers
        $this->headers = [
            "Authorization: Bearer {$this->accessToken}",
            "Content-Type: application/json"
        ];
    }

    /**
     * Send quick SMS to one or multiple recipients.
     */
    public function sendQuickSMS($senderId = null, $message, $recipients = [], $schedule = false, $scheduledFor = null, $scheduleName = null) {
        $url = $this->baseUrl . "sms/quick-send";

        // Use default sender ID if none provided
        $senderId = $senderId ?? $this->defaultSenderId;

        $payload = [
            "sender_id"   => $senderId,
            "message"     => $message,
            "recipients"  => implode("\n", $recipients),
            "schedule"    => $schedule
        ];

        if ($schedule && $scheduledFor) {
            $payload["scheduled_for"] = $scheduledFor;
        }

        if ($scheduleName) {
            $payload["schedule_name"] = $scheduleName;
        }

        return $this->sendRequest("POST", $url, $payload);
    }

    /**
     * Send SMS to a contact group.
     */
    public function sendGroupSMS($senderId = null, $message, $groupUuid, $schedule = false, $scheduledFor = null, $scheduleName = null) {
        $url = $this->baseUrl . "sms/quick-send/group";

        // Use default sender ID if none provided
        $senderId = $senderId ?? $this->defaultSenderId;

        $payload = [
            "sender_id"  => $senderId,
            "message"    => $message,
            "group_uuid" => $groupUuid,
            "schedule"   => $schedule
        ];

        if ($schedule && $scheduledFor) {
            $payload["scheduled_for"] = $scheduledFor;
        }

        if ($scheduleName) {
            $payload["schedule_name"] = $scheduleName;
        }

        return $this->sendRequest("POST", $url, $payload);
    }

    /**
     * Fetch all sender IDs.
     */
    public function getSenderIds() {
        $url = $this->baseUrl . "sender-ids";
        return $this->sendRequest("GET", $url);
    }

    /**
     * Reusable function to handle API requests.
     */
    private function sendRequest($method, $url, $payload = null) {
        $ch = curl_init();

        $options = [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER     => $this->headers
        ];

        if ($method === "POST") {
            $options[CURLOPT_POST]       = true;
            $options[CURLOPT_POSTFIELDS] = json_encode($payload);
        }

        curl_setopt_array($ch, $options);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            return [
                "success" => false,
                "error"   => curl_error($ch)
            ];
        }

        curl_close($ch);

        return json_decode($response, true);
    }
}

// Function to decrypt and validate IDs
function processId($id, $paramName) {
    if ($id) {
        try {
            // Attempt to decrypt the ID
            $id = decryptData($id);

            // Further validation
            if (empty($id) || !preg_match('/^[a-zA-Z0-9]+$/', $id)) {
                header("Location: /error.php?message=Invalid " . urlencode($paramName) . " Selected.");
                exit;
            }
            return $id;
        } catch (Exception $e) {
            // Handle decryption failure
            header("Location: /error.php?message=" . urlencode($e->getMessage()));
            exit;
        }
    }
    return null;
}

// Validate target-specific ID requirements
function validateTarget($target, $sub_parish_id, $community_id, $group_id) {
    switch ($target) {
        case 'head-parish':
            break;
        case 'sub-parish':
            if (empty($sub_parish_id)) {
                header("Location: /error.php?message=" . urlencode("Sub Parish ID is required for sub-parish target."));
                exit();
            }
            break;
        case 'community':
            if (empty($sub_parish_id) || empty($community_id)) {
                header("Location: /error.php?message=" . urlencode("Both Sub Parish ID and Community ID are required for community target."));
                exit();
            }
            break;
        case 'groups':
            if (empty($group_id)) {
                header("Location: /error.php?message=" . urlencode("Group ID is required for group target."));
                exit();
            }
            break;
        default:
            header("Location: /error.php?message=" . urlencode("Invalid target type."));
            exit();
    }
}

function getOrdinal($number) {
    $ordinal = '';
    
    if ($number == 1) {
        $ordinal = 'First';
    } elseif ($number == 2) {
        $ordinal = 'Second';
    } elseif ($number == 3) {
        $ordinal = 'Third';
    } elseif ($number == 4) {
        $ordinal = 'Fourth';
    } elseif ($number == 5) {
        $ordinal = 'Fifth';
    } elseif ($number == 6) {
        $ordinal = 'Sixth';
    } elseif ($number == 7) {
        $ordinal = 'Seventh';
    } elseif ($number == 8) {
        $ordinal = 'Eighth';
    } elseif ($number == 9) {
        $ordinal = 'Ninth';
    } elseif ($number == 10) {
        $ordinal = 'Tenth';
    } else {
        $ordinal = 'Invalid number';
    }

    return $ordinal;
}



// FINANCIAL STATEMENT
function getAverageAttendance($year, $headParishId, $conn) {
    // Prepare the SQL query to calculate the total average attendance
    $query = "SELECT 
                ROUND(AVG(male_attendance + female_attendance)) AS avg_adult_attendance, 
                ROUND(AVG(children_attendance)) AS avg_children_attendance 
              FROM head_parish_attendance 
              WHERE head_parish_id = ? AND YEAR(attendance_date) = ?";

    // Prepare the statement
    if ($stmt = $conn->prepare($query)) {
        // Bind the parameters
        $stmt->bind_param("ii", $headParishId, $year);
        
        // Execute the statement
        $stmt->execute();
        
        // Bind result variables
        $stmt->bind_result($avgAdultAttendance, $avgChildrenAttendance);
        
        // Fetch the result
        if ($stmt->fetch()) {
            return [
                "average_adult_attendance" => $avgAdultAttendance ?? 0, 
                "average_children_attendance" => $avgChildrenAttendance ?? 0
            ];
        } else {
            return [
                "average_adult_attendance" => 0, 
                "average_children_attendance" => 0
            ];
        }
        
        // Close the statement
        $stmt->close();
    } else {
        return [
            "average_adult_attendance" => 0, 
            "average_children_attendance" => 0
        ];
    }
}

function getAttendanceBenchmark($headParishId, $conn) {
    // Prepare the SQL query to fetch the benchmarks for the given head parish ID
    $query = "SELECT adult_reading, child_reading FROM head_parish_benchmark WHERE head_parish_id = ?";

    // Prepare the statement
    if ($stmt = $conn->prepare($query)) {
        // Bind the parameter
        $stmt->bind_param("i", $headParishId);
        
        // Execute the statement
        $stmt->execute();
        
        // Bind result variables
        $stmt->bind_result($adultReading, $childReading);
        
        // Fetch the result
        if ($stmt->fetch()) {
            return [
                "adult_reading" => $adultReading ?? 1000, // Default to 1000 if null
                "child_reading" => $childReading ?? 1000  // Default to 1000 if null
            ];
        } else {
            return ["adult_reading" => 1000, "child_reading" => 1000]; // Default values if not set
        }
        
        // Close the statement
        $stmt->close();
    } else {
        return ["adult_reading" => 1000, "child_reading" => 1000]; // Default values if query fails
    }
}



function getHeadParishRevenueGroupFinancials($headParishId, $year, $conn) {
    $revenueGroupsData = [];

    // Get all revenue groups for the provided head parish ID
    $groupQuery = "SELECT hprg.revenue_group_id, hprg.revenue_group_name, hpba.account_name 
               FROM head_parish_revenue_groups hprg
               LEFT JOIN head_parish_bank_accounts hpba ON hprg.account_id = hpba.account_id
               WHERE hprg.head_parish_id = $headParishId";

    $groupResult = mysqli_query($conn, $groupQuery);
    
    if ($groupResult) {
        while ($groupRow = mysqli_fetch_assoc($groupResult)) {
            $revenueGroupId = $groupRow['revenue_group_id'];
            $revenueGroupName = $groupRow['revenue_group_name'];

            // Fetch all revenue streams mapped to this revenue group
            $streamQuery = "SELECT revenue_stream_id FROM head_parish_revenue_groups_map WHERE revenue_group_id = $revenueGroupId";
            $streamResult = mysqli_query($conn, $streamQuery);
            
            $revenueStreamIds = [];
            if ($streamResult) {
                while ($streamRow = mysqli_fetch_assoc($streamResult)) {
                    $revenueStreamIds[] = $streamRow['revenue_stream_id'];
                }
            }
            
            // If there are revenue stream IDs, proceed
            if (!empty($revenueStreamIds)) {
                $streamIdsPlaceholder = implode(',', $revenueStreamIds);

                // Fetch total revenue target (budget) for the given year
                $budgetQuery = "SELECT SUM(revenue_target_amount) FROM head_parish_revenue_stream_targets 
                                WHERE head_parish_id = $headParishId 
                                AND revenue_stream_id IN ($streamIdsPlaceholder) 
                                AND YEAR(revenue_target_start_date) = $year";
                $budgetResult = mysqli_query($conn, $budgetQuery);
                $totalBudget = 0;
                if ($budgetResult) {
                    $budgetRow = mysqli_fetch_row($budgetResult);
                    $totalBudget = $budgetRow[0] ?? 0;
                }

                // Fetch total revenue collected from head_parish_revenues and other_head_parish_revenues
                $revenueQuery = "SELECT SUM(revenue_amount) FROM (
                                    SELECT revenue_amount FROM head_parish_revenues WHERE head_parish_id = $headParishId 
                                    AND revenue_stream_id IN ($streamIdsPlaceholder) 
                                    AND YEAR(revenue_date) = $year
                                    UNION ALL
                                    SELECT revenue_amount FROM other_head_parish_revenues WHERE head_parish_id = $headParishId 
                                    AND revenue_stream_id IN ($streamIdsPlaceholder) 
                                    AND YEAR(revenue_date) = $year
                                ) AS combined_revenue";
                $revenueResult = mysqli_query($conn, $revenueQuery);
                $totalRevenueCollected = 0;
                if ($revenueResult) {
                    $revenueRow = mysqli_fetch_row($revenueResult);
                    $totalRevenueCollected = $revenueRow[0] ?? 0;
                }

                // Store data in an array
                $revenueGroupsData[] = [
                    'revenue_group_id' => $revenueGroupId,
                    'revenue_group_name' => $revenueGroupName,
                    'account_name' => formatExpenseGroupName($groupRow['account_name']) ?? 'N/A',
                    'total_budget' => $totalBudget,
                    'total_revenue_collected' => $totalRevenueCollected
                ];
            }
        }

        // Free the result sets
        mysqli_free_result($groupResult);
    }

    return $revenueGroupsData;
}

function calculateTotalAmount($data, $key) {
    $total = 0;

    foreach ($data as $item) {
        if (isset($item[$key])) {
            $total += $item[$key];
        }
    }

    return $total;
}


function getRevenueDataByHeadParish($headParishId, $year, $conn) {
    $revenueGroupsData = [];

    // Sub-parish: Sum of revenue targets
    $subParishTargetQuery = "SELECT SUM(revenue_target_amount) AS total_target
                             FROM sub_parish_revenue_stream_targets
                             WHERE head_parish_id = $headParishId
                             AND YEAR(revenue_target_start_date) <= $year
                             AND YEAR(revenue_target_end_date) >= $year";
    $subParishTargetResult = mysqli_query($conn, $subParishTargetQuery);
    $subParishTarget = 0;
    if ($subParishTargetResult) {
        $subParishTargetRow = mysqli_fetch_assoc($subParishTargetResult);
        $subParishTarget = $subParishTargetRow['total_target'] ?? 0;
    }

    // Sub-parish: Sum of revenue collected
    $subParishRevenueQuery = "SELECT SUM(revenue_amount) AS total_collected
                              FROM sub_parish_revenues
                              WHERE head_parish_id = $headParishId
                              AND YEAR(revenue_date) = $year";
    $subParishRevenueResult = mysqli_query($conn, $subParishRevenueQuery);
    $subParishRevenue = 0;
    if ($subParishRevenueResult) {
        $subParishRevenueRow = mysqli_fetch_assoc($subParishRevenueResult);
        $subParishRevenue = $subParishRevenueRow['total_collected'] ?? 0;
    }

    // Add sub-parish data to the results
    $revenueGroupsData[] = [
        'category_id' => 1,
        'category_name' => 'Mitaa',  // 'Mitaa' is used for sub-parish
        'total_budget' => $subParishTarget,
        'total_revenue_collected' => $subParishRevenue
    ];

    // Community: Sum of revenue targets
    $communityTargetQuery = "SELECT SUM(revenue_target_amount) AS total_target
                             FROM community_revenue_stream_targets
                             WHERE head_parish_id = $headParishId
                             AND YEAR(revenue_target_start_date) <= $year
                             AND YEAR(revenue_target_end_date) >= $year";
    $communityTargetResult = mysqli_query($conn, $communityTargetQuery);
    $communityTarget = 0;
    if ($communityTargetResult) {
        $communityTargetRow = mysqli_fetch_assoc($communityTargetResult);
        $communityTarget = $communityTargetRow['total_target'] ?? 0;
    }

    // Community: Sum of revenue collected
    $communityRevenueQuery = "SELECT SUM(revenue_amount) AS total_collected
                              FROM community_revenues
                              WHERE head_parish_id = $headParishId
                              AND YEAR(revenue_date) = $year";
    $communityRevenueResult = mysqli_query($conn, $communityRevenueQuery);
    $communityRevenue = 0;
    if ($communityRevenueResult) {
        $communityRevenueRow = mysqli_fetch_assoc($communityRevenueResult);
        $communityRevenue = $communityRevenueRow['total_collected'] ?? 0;
    }

    // Add community data to the results
    $revenueGroupsData[] = [
        'category_id' => 2,
        'category_name' => 'Jumuiya',  // 'Jumuiya' is used for community
        'total_budget' => $communityTarget,
        'total_revenue_collected' => $communityRevenue
    ];

    // Group: Sum of revenue targets
    $groupTargetQuery = "SELECT SUM(revenue_target_amount) AS total_target
                         FROM group_revenue_stream_targets
                         WHERE head_parish_id = $headParishId
                         AND YEAR(revenue_target_start_date) <= $year
                         AND YEAR(revenue_target_end_date) >= $year";
    $groupTargetResult = mysqli_query($conn, $groupTargetQuery);
    $groupTarget = 0;
    if ($groupTargetResult) {
        $groupTargetRow = mysqli_fetch_assoc($groupTargetResult);
        $groupTarget = $groupTargetRow['total_target'] ?? 0;
    }

    // Group: Sum of revenue collected
    $groupRevenueQuery = "SELECT SUM(revenue_amount) AS total_collected
                          FROM group_revenues
                          WHERE head_parish_id = $headParishId
                          AND YEAR(revenue_date) = $year";
    $groupRevenueResult = mysqli_query($conn, $groupRevenueQuery);
    $groupRevenue = 0;
    if ($groupRevenueResult) {
        $groupRevenueRow = mysqli_fetch_assoc($groupRevenueResult);
        $groupRevenue = $groupRevenueRow['total_collected'] ?? 0;
    }

    // Add group data to the results
    $revenueGroupsData[] = [
        'category_id' => 3,
        'category_name' => 'Vikundi',  // 'Vikundi' is used for groups
        'total_budget' => $groupTarget,
        'total_revenue_collected' => $groupRevenue
    ];

    return $revenueGroupsData;
}


function getExpenseDataByHeadParish($headParishId, $year, $conn) {
    $expenseGroupsData = [];

    // Fetch all expense groups for the given head parish
    $expenseGroupQuery = "SELECT expense_group_id, expense_group_name
                          FROM head_parish_expense_groups
                          WHERE head_parish_id = $headParishId";
    $expenseGroupResult = mysqli_query($conn, $expenseGroupQuery);

    // Iterate through each expense group
    while ($expenseGroupRow = mysqli_fetch_assoc($expenseGroupResult)) {
        $expenseGroupId = $expenseGroupRow['expense_group_id'];
        $expenseGroupName = $expenseGroupRow['expense_group_name'];

        // Sum of all expense budgets for the given expense group
        $expenseBudgetQuery = "SELECT SUM(budgeted_amount) AS total_budget
                               FROM head_parish_expense_budgets
                               WHERE head_parish_id = $headParishId
                               AND expense_group_id = $expenseGroupId
                               AND YEAR(start_date) <= $year
                               AND YEAR(end_date) >= $year";
        $expenseBudgetResult = mysqli_query($conn, $expenseBudgetQuery);
        $totalBudget = 0;
        if ($expenseBudgetResult) {
            $expenseBudgetRow = mysqli_fetch_assoc($expenseBudgetResult);
            $totalBudget = $expenseBudgetRow['total_budget'] ?? 0;
        }

        // Sum of approved expenses for the given expense group (considering approved status)
        $approvedExpenseQuery = "SELECT SUM(request_amount) AS total_approved
                                 FROM head_parish_expense_requests
                                 WHERE head_parish_id = $headParishId
                                 AND expense_group_id = $expenseGroupId
                                 AND request_status = 'Approved'
                                 AND YEAR(request_datetime) = $year";
        $approvedExpenseResult = mysqli_query($conn, $approvedExpenseQuery);
        $totalApprovedExpenses = 0;
        if ($approvedExpenseResult) {
            $approvedExpenseRow = mysqli_fetch_assoc($approvedExpenseResult);
            $totalApprovedExpenses = $approvedExpenseRow['total_approved'] ?? 0;
        }

        // Add data for this expense group to the result array
        $expenseGroupsData[] = [
            'category_id' => $expenseGroupId,
            'category_name' => formatExpenseGroupName($expenseGroupName),
            'total_budget' => $totalBudget,
            'total_approved_expenses' => $totalApprovedExpenses
        ];
    }

    return $expenseGroupsData;
}

function getHeadParishAssets($headParishId, $year, $conn) {
    // Prepare the SQL query to fetch asset data with net revenue calculation
    $query = "
        SELECT 
            a.asset_name, 
            a.generates_revenue, 
            s.status, 
            s.description AS status_description, 
            COALESCE(SUM(r.revenue_amount), 0) - COALESCE(SUM(e.expense_amount), 0) AS net_revenue
        FROM head_parish_assets a
        LEFT JOIN head_parish_asset_status s ON a.asset_id = s.asset_id 
            AND YEAR(s.status_date) = ?
        LEFT JOIN head_parish_asset_revenues r ON a.asset_id = r.asset_id 
            AND YEAR(r.revenue_date) = ?
        LEFT JOIN head_parish_asset_expenses e ON a.asset_id = e.asset_id 
            AND YEAR(e.expense_date) = ?
        WHERE a.head_parish_id = ?
        GROUP BY a.asset_id, s.status, s.description
    ";

    // Prepare statement
    if ($stmt = $conn->prepare($query)) {
        // Bind parameters
        $stmt->bind_param('iiis', $year, $year, $year, $headParishId);

        // Execute the query
        $stmt->execute();

        // Bind result variables
        $stmt->bind_result($assetName, $generatesRevenue, $status, $statusDescription, $netRevenue);

        // Fetch data
        $assetsData = [];
        while ($stmt->fetch()) {
            $assetsData[] = [
                'asset_name' => $assetName,
                'generates_revenue' => $generatesRevenue ? 'Yes' : 'No',
                'status' => $status,
                'status_description' => $statusDescription,
                'generated_revenue' => $netRevenue ? $netRevenue : '0.00' // Now reflecting net revenue
            ];
        }

        // Close the statement
        $stmt->close();
    } else {
        // Handle error if the query fails
        die('Query failed: ' . $conn->error);
    }

    return $assetsData;
}



// QUARTERLY
function getQuarterlyAverageAttendance($startDate, $endDate, $headParishId, $conn) {
    // Prepare the SQL query to calculate the total average attendance within the given quarter
    $query = "SELECT 
                ROUND(AVG(male_attendance + female_attendance)) AS avg_adult_attendance, 
                ROUND(AVG(children_attendance)) AS avg_children_attendance 
              FROM head_parish_attendance 
              WHERE head_parish_id = ? 
              AND attendance_date BETWEEN ? AND ?";

    // Prepare the statement
    if ($stmt = $conn->prepare($query)) {
        // Bind the parameters
        $stmt->bind_param("iss", $headParishId, $startDate, $endDate);
        
        // Execute the statement
        $stmt->execute();
        
        // Bind result variables
        $stmt->bind_result($avgAdultAttendance, $avgChildrenAttendance);
        
        // Fetch the result
        if ($stmt->fetch()) {
            return [
                "average_adult_attendance" => $avgAdultAttendance ?? 0, 
                "average_children_attendance" => $avgChildrenAttendance ?? 0
            ];
        } else {
            return [
                "average_adult_attendance" => 0, 
                "average_children_attendance" => 0
            ];
        }
        
        // Close the statement
        $stmt->close();
    } else {
        return [
            "average_adult_attendance" => 0, 
            "average_children_attendance" => 0
        ];
    }
}


function getHeadParishQuarterlyRevenueGroupFinancials($headParishId, $year, $quarterStartDate, $quarterEndDate, $conn) {
    $revenueGroupsData = [];

    // Get all revenue groups for the provided head parish ID
    $groupQuery = "SELECT hprg.revenue_group_id, hprg.revenue_group_name, hpba.account_name 
               FROM head_parish_revenue_groups hprg
               LEFT JOIN head_parish_bank_accounts hpba ON hprg.account_id = hpba.account_id
               WHERE hprg.head_parish_id = $headParishId";

    $groupResult = mysqli_query($conn, $groupQuery);
    
    if ($groupResult) {
        while ($groupRow = mysqli_fetch_assoc($groupResult)) {
            $revenueGroupId = $groupRow['revenue_group_id'];
            $revenueGroupName = $groupRow['revenue_group_name'];

            // Fetch all revenue streams mapped to this revenue group
            $streamQuery = "SELECT revenue_stream_id FROM head_parish_revenue_groups_map WHERE revenue_group_id = $revenueGroupId";
            $streamResult = mysqli_query($conn, $streamQuery);
            
            $revenueStreamIds = [];
            if ($streamResult) {
                while ($streamRow = mysqli_fetch_assoc($streamResult)) {
                    $revenueStreamIds[] = $streamRow['revenue_stream_id'];
                }
            }
            
            // If there are revenue stream IDs, proceed
            if (!empty($revenueStreamIds)) {
                $streamIdsPlaceholder = implode(',', $revenueStreamIds);

                // Fetch total revenue target (budget) for the given quarter
                $budgetQuery = "SELECT SUM(revenue_target_amount) FROM head_parish_revenue_stream_targets 
                                WHERE head_parish_id = $headParishId 
                                AND revenue_stream_id IN ($streamIdsPlaceholder) 
                                AND YEAR(revenue_target_start_date) = '$year'";
                $budgetResult = mysqli_query($conn, $budgetQuery);
                $totalBudget = 0;
                if ($budgetResult) {
                    $budgetRow = mysqli_fetch_row($budgetResult);
                    $totalBudget = $budgetRow[0] ?? 0;
                }

                // Fetch total revenue collected from head_parish_revenues and other_head_parish_revenues
                $revenueQuery = "SELECT SUM(revenue_amount) FROM (
                                    SELECT revenue_amount FROM head_parish_revenues WHERE head_parish_id = $headParishId 
                                    AND revenue_stream_id IN ($streamIdsPlaceholder) 
                                    AND revenue_date BETWEEN '$quarterStartDate' AND '$quarterEndDate'
                                    UNION ALL
                                    SELECT revenue_amount FROM other_head_parish_revenues WHERE head_parish_id = $headParishId 
                                    AND revenue_stream_id IN ($streamIdsPlaceholder) 
                                    AND revenue_date BETWEEN '$quarterStartDate' AND '$quarterEndDate'
                                ) AS combined_revenue";
                $revenueResult = mysqli_query($conn, $revenueQuery);
                $totalRevenueCollected = 0;
                if ($revenueResult) {
                    $revenueRow = mysqli_fetch_row($revenueResult);
                    $totalRevenueCollected = $revenueRow[0] ?? 0;
                }

                // Store data in an array
                $revenueGroupsData[] = [
                    'revenue_group_id' => $revenueGroupId,
                    'revenue_group_name' => $revenueGroupName,
                    'account_name' => formatExpenseGroupName($groupRow['account_name']) ?? 'N/A',
                    'total_budget' => $totalBudget/4,
                    'total_revenue_collected' => $totalRevenueCollected
                ];
            }
        }

        // Free the result sets
        mysqli_free_result($groupResult);
    }

    return $revenueGroupsData;
}

function getRevenueDataByHeadParishQuarterly($headParishId, $year, $quarterStartDate, $quarterEndDate, $conn) {
    $revenueGroupsData = [];

    // Sub-parish: Sum of revenue targets
    $subParishTargetQuery = "SELECT SUM(revenue_target_amount) AS total_target
                             FROM sub_parish_revenue_stream_targets
                             WHERE head_parish_id = $headParishId
                             AND YEAR(revenue_target_start_date) = '$year'
                             AND YEAR(revenue_target_end_date) = '$year'";
    $subParishTargetResult = mysqli_query($conn, $subParishTargetQuery);
    $subParishTarget = 0;
    if ($subParishTargetResult) {
        $subParishTargetRow = mysqli_fetch_assoc($subParishTargetResult);
        $subParishTarget = $subParishTargetRow['total_target'] ?? 0;
    }

    // Sub-parish: Sum of revenue collected
    $subParishRevenueQuery = "SELECT SUM(revenue_amount) AS total_collected
                              FROM sub_parish_revenues
                              WHERE head_parish_id = $headParishId
                              AND revenue_date BETWEEN '$quarterStartDate' AND '$quarterEndDate'";
    $subParishRevenueResult = mysqli_query($conn, $subParishRevenueQuery);
    $subParishRevenue = 0;
    if ($subParishRevenueResult) {
        $subParishRevenueRow = mysqli_fetch_assoc($subParishRevenueResult);
        $subParishRevenue = $subParishRevenueRow['total_collected'] ?? 0;
    }

    // Add sub-parish data to the results
    $revenueGroupsData[] = [
        'category_id' => 1,
        'category_name' => 'Mitaa',  // 'Mitaa' is used for sub-parish
        'total_budget' => $subParishTarget/4,
        'total_revenue_collected' => $subParishRevenue
    ];

    // Community: Sum of revenue targets
    $communityTargetQuery = "SELECT SUM(revenue_target_amount) AS total_target
                             FROM community_revenue_stream_targets
                             WHERE head_parish_id = $headParishId
                            AND YEAR(revenue_target_start_date) = '$year'
                             AND YEAR(revenue_target_end_date) = '$year'";
    $communityTargetResult = mysqli_query($conn, $communityTargetQuery);
    $communityTarget = 0;
    if ($communityTargetResult) {
        $communityTargetRow = mysqli_fetch_assoc($communityTargetResult);
        $communityTarget = $communityTargetRow['total_target'] ?? 0;
    }

    // Community: Sum of revenue collected
    $communityRevenueQuery = "SELECT SUM(revenue_amount) AS total_collected
                              FROM community_revenues
                              WHERE head_parish_id = $headParishId
                              AND revenue_date BETWEEN '$quarterStartDate' AND '$quarterEndDate'";
    $communityRevenueResult = mysqli_query($conn, $communityRevenueQuery);
    $communityRevenue = 0;
    if ($communityRevenueResult) {
        $communityRevenueRow = mysqli_fetch_assoc($communityRevenueResult);
        $communityRevenue = $communityRevenueRow['total_collected'] ?? 0;
    }

    // Add community data to the results
    $revenueGroupsData[] = [
        'category_id' => 2,
        'category_name' => 'Jumuiya',  // 'Jumuiya' is used for community
        'total_budget' => $communityTarget/4,
        'total_revenue_collected' => $communityRevenue
    ];

    // Group: Sum of revenue targets
    $groupTargetQuery = "SELECT SUM(revenue_target_amount) AS total_target
                         FROM group_revenue_stream_targets
                         WHERE head_parish_id = $headParishId
                         AND YEAR(revenue_target_start_date) = '$year' AND YEAR(revenue_target_end_date) = '$year'";
    $groupTargetResult = mysqli_query($conn, $groupTargetQuery);
    $groupTarget = 0;
    if ($groupTargetResult) {
        $groupTargetRow = mysqli_fetch_assoc($groupTargetResult);
        $groupTarget = $groupTargetRow['total_target'] ?? 0;
    }

    // Group: Sum of revenue collected
    $groupRevenueQuery = "SELECT SUM(revenue_amount) AS total_collected
                          FROM group_revenues
                          WHERE head_parish_id = $headParishId
                          AND revenue_date BETWEEN '$quarterStartDate' AND '$quarterEndDate'";
    $groupRevenueResult = mysqli_query($conn, $groupRevenueQuery);
    $groupRevenue = 0;
    if ($groupRevenueResult) {
        $groupRevenueRow = mysqli_fetch_assoc($groupRevenueResult);
        $groupRevenue = $groupRevenueRow['total_collected'] ?? 0;
    }

    // Add group data to the results
    $revenueGroupsData[] = [
        'category_id' => 3,
        'category_name' => 'Vikundi',  // 'Vikundi' is used for groups
        'total_budget' => $groupTarget/4,
        'total_revenue_collected' => $groupRevenue
    ];

    return $revenueGroupsData;
}

function getExpenseDataByHeadParishQuarterly($headParishId, $year, $quarterStartDate, $quarterEndDate, $conn) {
    $expenseGroupsData = [];

    // Fetch all expense groups for the given head parish
    $expenseGroupQuery = "SELECT expense_group_id, expense_group_name
                          FROM head_parish_expense_groups
                          WHERE head_parish_id = $headParishId";
    $expenseGroupResult = mysqli_query($conn, $expenseGroupQuery);

    // Iterate through each expense group
    while ($expenseGroupRow = mysqli_fetch_assoc($expenseGroupResult)) {
        $expenseGroupId = $expenseGroupRow['expense_group_id'];
        $expenseGroupName = $expenseGroupRow['expense_group_name'];

        // Sum of all expense budgets for the given expense group
        $expenseBudgetQuery = "SELECT SUM(budgeted_amount) AS total_budget
                               FROM head_parish_expense_budgets
                               WHERE head_parish_id = $headParishId
                               AND expense_group_id = $expenseGroupId
                               AND YEAR(start_date) = '$year' 
                               AND YEAR(end_date) = '$year'";
        $expenseBudgetResult = mysqli_query($conn, $expenseBudgetQuery);
        $totalBudget = 0;
        if ($expenseBudgetResult) {
            $expenseBudgetRow = mysqli_fetch_assoc($expenseBudgetResult);
            $totalBudget = $expenseBudgetRow['total_budget'] ?? 0;
        }

        // Sum of approved expenses for the given expense group (considering approved status)
        $approvedExpenseQuery = "SELECT SUM(request_amount) AS total_approved
                                 FROM head_parish_expense_requests
                                 WHERE head_parish_id = $headParishId
                                 AND expense_group_id = $expenseGroupId
                                 AND request_status = 'Approved'
                                 AND request_datetime BETWEEN '$quarterStartDate' AND '$quarterEndDate'";
        $approvedExpenseResult = mysqli_query($conn, $approvedExpenseQuery);
        $totalApprovedExpenses = 0;
        if ($approvedExpenseResult) {
            $approvedExpenseRow = mysqli_fetch_assoc($approvedExpenseResult);
            $totalApprovedExpenses = $approvedExpenseRow['total_approved'] ?? 0;
        }

        // Add data for this expense group to the result array
        $expenseGroupsData[] = [
            'category_id' => $expenseGroupId,
            'category_name' => formatExpenseGroupName($expenseGroupName),
            'total_budget' => $totalBudget/4,
            'total_approved_expenses' => $totalApprovedExpenses
        ];
    }

    return $expenseGroupsData;
}

function getHeadParishAssetsQuarterly($headParishId, $quarterStartDate, $quarterEndDate, $conn) {
    // Prepare the SQL query to fetch asset data along with net revenue calculation
    $query = "
        SELECT 
            a.asset_name, 
            a.generates_revenue, 
            s.status, 
            s.description AS status_description, 
            COALESCE(SUM(r.revenue_amount), 0) - COALESCE(SUM(e.expense_amount), 0) AS net_revenue
        FROM head_parish_assets a
        LEFT JOIN head_parish_asset_status s ON a.asset_id = s.asset_id 
            AND s.status_date BETWEEN ? AND ?
        LEFT JOIN head_parish_asset_revenues r ON a.asset_id = r.asset_id 
            AND r.revenue_date BETWEEN ? AND ?
        LEFT JOIN head_parish_asset_expenses e ON a.asset_id = e.asset_id 
            AND e.expense_date BETWEEN ? AND ?
        WHERE a.head_parish_id = ?
        GROUP BY a.asset_id, s.status, s.description
    ";

    // Prepare statement
    if ($stmt = $conn->prepare($query)) {
        // Bind parameters
        $stmt->bind_param('ssssssi', $quarterStartDate, $quarterEndDate, $quarterStartDate, $quarterEndDate, $quarterStartDate, $quarterEndDate, $headParishId);

        // Execute the query
        $stmt->execute();

        // Bind result variables
        $stmt->bind_result($assetName, $generatesRevenue, $status, $statusDescription, $netRevenue);

        // Fetch data
        $assetsData = [];
        while ($stmt->fetch()) {
            $assetsData[] = [
                'asset_name' => $assetName,
                'generates_revenue' => $generatesRevenue ? 'Yes' : 'No',
                'status' => $status,
                'status_description' => $statusDescription,
                'generated_revenue' => $netRevenue ? $netRevenue : '0.00' // Net revenue calculation
            ];
        }

        // Close the statement
        $stmt->close();
    } else {
        // Handle error if the query fails
        die('Query failed: ' . $conn->error);
    }

    return $assetsData;
}



function formatExpenseGroupName($name) {
    // Convert entire string to lowercase
    $name = mb_strtolower($name);

    // Capitalize the first letter of each word
    $name = ucwords($name);

    // Define words that should remain lowercase
    $lowercaseWords = ['ya', 'na', 'la', 'wa', 'kwa', 'katika', 'cha', 'za', 'ku'];

    // Convert specific words back to lowercase
    $words = explode(' ', $name);
    foreach ($words as $key => $word) {
        if (in_array(mb_strtolower($word), $lowercaseWords)) {
            $words[$key] = mb_strtolower($word);
        }
    }

    return implode(' ', $words);
}



function getEnvelopeContributionBySubParish($headParishId, $year, $conn) {
    $envelopeData = [];

    // Get all sub-parishes for the specified head parish
    $subParishQuery = "SELECT sub_parish_id, sub_parish_name
                       FROM sub_parishes
                       WHERE head_parish_id = $headParishId";
    $subParishResult = mysqli_query($conn, $subParishQuery);

    if ($subParishResult) {
        while ($subParishRow = mysqli_fetch_assoc($subParishResult)) {
            $subParishId = $subParishRow['sub_parish_id'];
            $subParishName = $subParishRow['sub_parish_name'];

            // Get total contribution for the sub-parish in the specified year
            $contributionQuery = "SELECT SUM(amount) AS total_contributed,
                                         COUNT(DISTINCT member_id) AS unique_members
                                  FROM envelope_contribution
                                  WHERE head_parish_id = $headParishId
                                  AND sub_parish_id = $subParishId
                                  AND YEAR(contribution_date) = $year";
            $contributionResult = mysqli_query($conn, $contributionQuery);

            if ($contributionResult) {
                $contributionRow = mysqli_fetch_assoc($contributionResult);
                $totalContributed = $contributionRow['total_contributed'] ?? 0;
                $uniqueMembers = $contributionRow['unique_members'] ?? 0;

                // Add sub-parish data to the results
                $envelopeData[] = [
                    'sub_parish_name' => $subParishName,
                    'total_contributed' => $totalContributed,
                    'unique_members' => $uniqueMembers
                ];
            }
        }
    }

    return $envelopeData;
}


function getRevenueStreamName($conn, $headParishId, $revenueStreamId) {
    $query = "SELECT revenue_stream_name 
              FROM head_parish_revenue_streams 
              WHERE head_parish_id = ? AND revenue_stream_id = ? 
              LIMIT 1";

    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("ii", $headParishId, $revenueStreamId);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($row = $result->fetch_assoc()) {
            return $row['revenue_stream_name'];
        }
    }

    return null; // Or return 'Unknown' if you prefer a default string
}


function getHeadParishRevenueBySubParish($headParishId, $year, $accountId, $conn) {
    $revenues = [];

    // Fetch all revenue streams for the head parish and account
    $streamQuery = "SELECT revenue_stream_id, revenue_stream_name 
                    FROM head_parish_revenue_streams 
                    WHERE head_parish_id = ? AND account_id = ?";

    if ($stmt = mysqli_prepare($conn, $streamQuery)) {
        mysqli_stmt_bind_param($stmt, "ii", $headParishId, $accountId);
        mysqli_stmt_execute($stmt);
        $streamResult = mysqli_stmt_get_result($stmt);

        while ($streamRow = mysqli_fetch_assoc($streamResult)) {
            $streamId = $streamRow['revenue_stream_id'];
            $streamName = $streamRow['revenue_stream_name'];

            // Query revenue totals per sub-parish
            $revenueQuery = "SELECT sp.sub_parish_id, sp.sub_parish_name, 
                                    SUM(hpr.revenue_amount) AS total_collected 
                             FROM head_parish_revenues hpr
                             LEFT JOIN sub_parishes sp ON hpr.sub_parish_id = sp.sub_parish_id
                             WHERE hpr.head_parish_id = ? 
                             AND hpr.revenue_stream_id = ? 
                             AND YEAR(hpr.revenue_date) = ?
                             GROUP BY sp.sub_parish_id, sp.sub_parish_name";

            if ($revenueStmt = mysqli_prepare($conn, $revenueQuery)) {
                mysqli_stmt_bind_param($revenueStmt, "iii", $headParishId, $streamId, $year);
                mysqli_stmt_execute($revenueStmt);
                $revenueResult = mysqli_stmt_get_result($revenueStmt);

                $items = [];
                while ($row = mysqli_fetch_assoc($revenueResult)) {
                    $amount = (float) $row['total_collected'];
                    if ($amount > 0) {
                        $items[] = [
                            'id' => $row['sub_parish_id'],
                            'name' => $row['sub_parish_name'],
                            'value' => $amount
                        ];
                    }
                }

                mysqli_stmt_close($revenueStmt);

                // Only add stream if there is at least one sub-parish with revenue
                if (!empty($items)) {
                    $revenues[] = [
                        'id' => $streamId,
                        'name' => $streamName,
                        'items' => $items
                    ];
                }
            }
        }

        mysqli_stmt_close($stmt);
    }

    return $revenues;
}


function getHeadParishRevenueBySubParishQuarterly($headParishId, $startDate, $endDate, $accountId, $conn) {
    $revenues = [];

    // Fetch all revenue streams for the head parish and account
    $streamQuery = "SELECT revenue_stream_id, revenue_stream_name 
                    FROM head_parish_revenue_streams 
                    WHERE head_parish_id = ? AND account_id = ?";

    if ($stmt = mysqli_prepare($conn, $streamQuery)) {
        mysqli_stmt_bind_param($stmt, "ii", $headParishId, $accountId);
        mysqli_stmt_execute($stmt);
        $streamResult = mysqli_stmt_get_result($stmt);

        while ($streamRow = mysqli_fetch_assoc($streamResult)) {
            $streamId = $streamRow['revenue_stream_id'];
            $streamName = $streamRow['revenue_stream_name'];

            // Query revenue totals per sub-parish within date range
            $revenueQuery = "SELECT sp.sub_parish_id, sp.sub_parish_name, 
                                    SUM(hpr.revenue_amount) AS total_collected 
                             FROM head_parish_revenues hpr
                             LEFT JOIN sub_parishes sp ON hpr.sub_parish_id = sp.sub_parish_id
                             WHERE hpr.head_parish_id = ? 
                             AND hpr.revenue_stream_id = ? 
                             AND hpr.revenue_date BETWEEN ? AND ?
                             GROUP BY sp.sub_parish_id, sp.sub_parish_name";

            if ($revenueStmt = mysqli_prepare($conn, $revenueQuery)) {
                mysqli_stmt_bind_param($revenueStmt, "iiss", $headParishId, $streamId, $startDate, $endDate);
                mysqli_stmt_execute($revenueStmt);
                $revenueResult = mysqli_stmt_get_result($revenueStmt);

                $items = [];
                while ($row = mysqli_fetch_assoc($revenueResult)) {
                    $amount = (float) $row['total_collected'];
                    if ($amount > 0) {
                        $items[] = [
                            'id' => $row['sub_parish_id'],
                            'name' => $row['sub_parish_name'],
                            'value' => $amount
                        ];
                    }
                }

                mysqli_stmt_close($revenueStmt);

                if (!empty($items)) {
                    $revenues[] = [
                        'id' => $streamId,
                        'name' => $streamName,
                        'items' => $items
                    ];
                }
            }
        }

        mysqli_stmt_close($stmt);
    }

    return $revenues;
}


function calculateTotalRevenue($revenues) {
    $totalRevenue = 0;
    
    // Loop through each revenue group and their sub-parish items
    foreach ($revenues as $revenueGroup) {
        foreach ($revenueGroup['items'] as $item) {
            $totalRevenue += $item['value']; // Add the revenue of each sub-parish to the total
        }
    }
    
    return $totalRevenue;
}



function getEnvelopeContributionsBySubParish($headParishId, $year, $conn) {
    $envelopeData = [];

    $query = "
        SELECT 
            sp.sub_parish_id,
            sp.sub_parish_name,
            COALESCE(SUM(ec.amount), 0) AS total_contribution,
            COUNT(DISTINCT ec.member_id) AS total_envelopes,
            COALESCE((
                SELECT SUM(et.target)
                FROM envelope_targets et
                WHERE et.sub_parish_id = sp.sub_parish_id
                AND et.head_parish_id = $headParishId
                AND YEAR(et.from_date) <= $year
                AND YEAR(et.end_date) >= $year
            ), 0) AS total_target
        FROM sub_parishes sp
        LEFT JOIN envelope_contribution ec 
            ON ec.sub_parish_id = sp.sub_parish_id 
            AND ec.head_parish_id = $headParishId
            AND YEAR(ec.contribution_date) = $year
        WHERE sp.head_parish_id = $headParishId
        GROUP BY sp.sub_parish_id, sp.sub_parish_name
        ORDER BY sp.sub_parish_name
    ";


    $result = mysqli_query($conn, $query);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $envelopeData[] = [
                'sub_parish_id' => $row['sub_parish_id'],
                'sub_parish_name' => $row['sub_parish_name'],
                'total_contribution' => (float) $row['total_contribution'],
                'total_envelopes' => (int) $row['total_envelopes'],
                'total_target' => (float) $row['total_target']
            ];
        }
        mysqli_free_result($result);
    }

    return $envelopeData;
}


function getEnvelopeContributionsBySubParishQuarterly($headParishId, $year, $startDate, $endDate, $conn) {
    $envelopeData = [];

   $query = "
        SELECT 
            sp.sub_parish_id,
            sp.sub_parish_name,
            COALESCE(SUM(ec.amount), 0) AS total_contribution,
            COUNT(DISTINCT ec.member_id) AS total_envelopes,
            COALESCE((
                SELECT SUM(et.target)
                FROM envelope_targets et
                WHERE et.sub_parish_id = sp.sub_parish_id
                AND et.head_parish_id = $headParishId
                AND YEAR(et.from_date) <= $year
                AND YEAR(et.end_date) >= $year
            ), 0) AS total_target
        FROM sub_parishes sp
        LEFT JOIN envelope_contribution ec 
            ON ec.sub_parish_id = sp.sub_parish_id 
            AND ec.head_parish_id = $headParishId
            AND ec.contribution_date BETWEEN '$startDate' AND '$endDate'
        WHERE sp.head_parish_id = $headParishId
        GROUP BY sp.sub_parish_id, sp.sub_parish_name
        ORDER BY sp.sub_parish_name
    ";


    $result = mysqli_query($conn, $query);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
             $target = (float) $row['total_target'];
            // Divide the target by 4 for the quarterly calculation
            $quarterlyTarget = $target / 4;
            
            $envelopeData[] = [
                'sub_parish_id' => $row['sub_parish_id'],
                'sub_parish_name' => $row['sub_parish_name'],
                'total_contribution' => (float) $row['total_contribution'],
                'total_envelopes' => (int) $row['total_envelopes'],
                'total_target' => (float) $quarterlyTarget
            ];
        }
        mysqli_free_result($result);
    }

    return $envelopeData;
}



function getAccountWiseFinancialSummary($headParishId, $year, $conn) {
    $financials = [];

    // Get all bank accounts for this head parish
    $accountQuery = "SELECT account_id, account_name FROM head_parish_bank_accounts WHERE head_parish_id = $headParishId";
    $accountResult = mysqli_query($conn, $accountQuery);

    if ($accountResult) {
        while ($account = mysqli_fetch_assoc($accountResult)) {
            $accountId = $account['account_id'];
            $accountName = $account['account_name'];

            // 1. Get opening balance from December of previous year
            $previousYear = $year - 1;
            $decemberDate = "$previousYear-12-31";
            $openingBalance = 0;

            $balanceQuery = "
                SELECT closing_balance 
                FROM head_parish_bank_account_closing_balances 
                WHERE account_id = $accountId 
                AND head_parish_id = $headParishId 
                AND balance_date = '$decemberDate'
            ";
            $balanceResult = mysqli_query($conn, $balanceQuery);
            if ($balanceResult && mysqli_num_rows($balanceResult) > 0) {
                $balanceRow = mysqli_fetch_assoc($balanceResult);
                $openingBalance = $balanceRow['closing_balance'];
            }

            // 2. Get total revenues collected for this account (from all revenue tables)
            $revenueQuery = "
            SELECT SUM(revenue_amount) AS total_revenue FROM (
                -- Head parish revenues
                SELECT r.revenue_amount
                FROM head_parish_revenues r
                INNER JOIN head_parish_revenue_streams s ON r.revenue_stream_id = s.revenue_stream_id
                WHERE r.head_parish_id = $headParishId 
                AND s.account_id = $accountId 
                AND YEAR(r.revenue_date) = $year
        
                UNION ALL
        
                -- Other head parish revenues
                SELECT r2.revenue_amount
                FROM other_head_parish_revenues r2
                INNER JOIN head_parish_revenue_streams s2 ON r2.revenue_stream_id = s2.revenue_stream_id
                WHERE r2.head_parish_id = $headParishId 
                AND s2.account_id = $accountId 
                AND YEAR(r2.revenue_date) = $year
        
                UNION ALL
        
                -- Sub parish revenues
                SELECT r3.revenue_amount
                FROM sub_parish_revenues r3
                INNER JOIN head_parish_revenue_streams s3 ON r3.revenue_stream_id = s3.revenue_stream_id
                WHERE r3.head_parish_id = $headParishId 
                AND s3.account_id = $accountId 
                AND YEAR(r3.revenue_date) = $year
        
                UNION ALL
        
                -- Community revenues
                SELECT r4.revenue_amount
                FROM community_revenues r4
                INNER JOIN head_parish_revenue_streams s4 ON r4.revenue_stream_id = s4.revenue_stream_id
                WHERE r4.head_parish_id = $headParishId 
                AND s4.account_id = $accountId 
                AND YEAR(r4.revenue_date) = $year
        
                UNION ALL
        
                -- Group revenues
                SELECT r5.revenue_amount
                FROM group_revenues r5
                INNER JOIN head_parish_revenue_streams s5 ON r5.revenue_stream_id = s5.revenue_stream_id
                WHERE r5.head_parish_id = $headParishId 
                AND s5.account_id = $accountId 
                AND YEAR(r5.revenue_date) = $year
            ) AS combined_revenues
        ";


            $totalRevenue = 0;
            $revenueResult = mysqli_query($conn, $revenueQuery);
            if ($revenueResult) {
                while ($revenueRow = mysqli_fetch_row($revenueResult)) {
                    $totalRevenue += $revenueRow[0] ?? 0;
                }
            }

            // 3. Get total approved expenses for this account from all expense tables
            $expenseQuery = "
                SELECT SUM(total_expense) AS total_expense FROM (
                    -- Head Parish
                    SELECT SUM(r.request_amount) AS total_expense
                    FROM head_parish_expense_requests r
                    INNER JOIN head_parish_expense_groups g ON r.expense_group_id = g.expense_group_id
                    WHERE r.head_parish_id = $headParishId
                    AND r.request_status = 'Approved'
                    AND g.account_id = $accountId
                    AND YEAR(r.request_datetime) = $year
            
                    UNION ALL
            
                    -- Sub Parish
                    SELECT SUM(r.request_amount)
                    FROM sub_parish_expense_requests r
                    INNER JOIN sub_parish_expense_groups g ON r.expense_group_id = g.expense_group_id
                    WHERE r.head_parish_id = $headParishId
                    AND r.request_status = 'Approved'
                    AND g.account_id = $accountId
                    AND YEAR(r.request_datetime) = $year
            
                    UNION ALL
            
                    -- Community
                    SELECT SUM(r.request_amount)
                    FROM community_expense_requests r
                    INNER JOIN community_expense_groups g ON r.expense_group_id = g.expense_group_id
                    WHERE r.head_parish_id = $headParishId
                    AND r.request_status = 'Approved'
                    AND g.account_id = $accountId
                    AND YEAR(r.request_datetime) = $year
            
                    UNION ALL
            
                    -- Group
                    SELECT SUM(r.request_amount)
                    FROM group_expense_requests r
                    INNER JOIN group_expense_groups g ON r.expense_group_id = g.expense_group_id
                    WHERE r.head_parish_id = $headParishId
                    AND r.request_status = 'Approved'
                    AND g.account_id = $accountId
                    AND YEAR(r.request_datetime) = $year
                ) AS combined_expenses
            ";

            $expenseResult = mysqli_query($conn, $expenseQuery);
            $totalExpense = 0;
            if ($expenseResult && mysqli_num_rows($expenseResult) > 0) {
                $expenseRow = mysqli_fetch_assoc($expenseResult);
                $totalExpense = $expenseRow['total_expense'] ?? 0;
            }

            // Store all values for this account
            $financials[] = [
                'account_id' => $accountId,
                'account_name' => $accountName,
                'opening_balance' => (float) $openingBalance,
                'total_revenue_collected' => (float) $totalRevenue,
                'total_approved_expenses' => (float) $totalExpense
            ];
        }

        mysqli_free_result($accountResult);
    }

    return $financials;
}


function getAccountWiseFinancialSummaryQuarterly($headParishId, $previousQuarterEndDate, $startDate, $endDate, $conn) {
    $financials = [];

    // Get all bank accounts for this head parish
    $accountQuery = "SELECT account_id, account_name FROM head_parish_bank_accounts WHERE head_parish_id = $headParishId";
    $accountResult = mysqli_query($conn, $accountQuery);

    if ($accountResult) {
        while ($account = mysqli_fetch_assoc($accountResult)) {
            $accountId = $account['account_id'];
            $accountName = $account['account_name'];

            // 1. Get opening balance from  previous quarter
            $openingBalance = 0;

            $balanceQuery = "
                SELECT closing_balance 
                FROM head_parish_bank_account_closing_balances 
                WHERE account_id = $accountId 
                AND head_parish_id = $headParishId 
                AND balance_date = '$previousQuarterEndDate'
            ";
            $balanceResult = mysqli_query($conn, $balanceQuery);
            if ($balanceResult && mysqli_num_rows($balanceResult) > 0) {
                $balanceRow = mysqli_fetch_assoc($balanceResult);
                $openingBalance = $balanceRow['closing_balance'];
            }

            // 2. Get total revenues collected for this account (from all revenue tables)
            $revenueQuery = "
            SELECT SUM(revenue_amount) AS total_revenue FROM (
                -- Head parish revenues
                SELECT r.revenue_amount
                FROM head_parish_revenues r
                INNER JOIN head_parish_revenue_streams s ON r.revenue_stream_id = s.revenue_stream_id
                WHERE r.head_parish_id = $headParishId 
                AND s.account_id = $accountId 
                AND r.revenue_date BETWEEN '$startDate' AND '$endDate'
                
                UNION ALL
                
                -- Other head parish revenues
                SELECT r2.revenue_amount
                FROM other_head_parish_revenues r2
                INNER JOIN head_parish_revenue_streams s2 ON r2.revenue_stream_id = s2.revenue_stream_id
                WHERE r2.head_parish_id = $headParishId 
                AND s2.account_id = $accountId 
                AND r2.revenue_date BETWEEN '$startDate' AND '$endDate'
                
                UNION ALL
                
                -- Sub parish revenues
                SELECT r3.revenue_amount
                FROM sub_parish_revenues r3
                INNER JOIN head_parish_revenue_streams s3 ON r3.revenue_stream_id = s3.revenue_stream_id
                WHERE r3.head_parish_id = $headParishId 
                AND s3.account_id = $accountId 
                AND r3.revenue_date BETWEEN '$startDate' AND '$endDate'
                
                UNION ALL
                
                -- Community revenues
                SELECT r4.revenue_amount
                FROM community_revenues r4
                INNER JOIN head_parish_revenue_streams s4 ON r4.revenue_stream_id = s4.revenue_stream_id
                WHERE r4.head_parish_id = $headParishId 
                AND s4.account_id = $accountId 
                AND r4.revenue_date BETWEEN '$startDate' AND '$endDate'
                
                UNION ALL
                
                -- Group revenues
                SELECT r5.revenue_amount
                FROM group_revenues r5
                INNER JOIN head_parish_revenue_streams s5 ON r5.revenue_stream_id = s5.revenue_stream_id
                WHERE r5.head_parish_id = $headParishId 
                AND s5.account_id = $accountId 
                AND r5.revenue_date BETWEEN '$startDate' AND '$endDate'
            ) AS combined_revenues
            ";


            $totalRevenue = 0;
            $revenueResult = mysqli_query($conn, $revenueQuery);
            if ($revenueResult) {
                while ($revenueRow = mysqli_fetch_row($revenueResult)) {
                    $totalRevenue += $revenueRow[0] ?? 0;
                }
            }

            // 3. Get total approved expenses for this account from all expense tables
            $expenseQuery = "
            SELECT SUM(total_expense) AS total_expense FROM (
                -- Head Parish
                SELECT SUM(r.request_amount) AS total_expense
                FROM head_parish_expense_requests r
                INNER JOIN head_parish_expense_groups g ON r.expense_group_id = g.expense_group_id
                WHERE r.head_parish_id = $headParishId
                AND r.request_status = 'Approved'
                AND g.account_id = $accountId
                AND r.request_datetime BETWEEN '$startDate' AND '$endDate'
            
                UNION ALL
            
                -- Sub Parish
                SELECT SUM(r.request_amount)
                FROM sub_parish_expense_requests r
                INNER JOIN sub_parish_expense_groups g ON r.expense_group_id = g.expense_group_id
                WHERE r.head_parish_id = $headParishId
                AND r.request_status = 'Approved'
                AND g.account_id = $accountId
                AND r.request_datetime BETWEEN '$startDate' AND '$endDate'
            
                UNION ALL
            
                -- Community
                SELECT SUM(r.request_amount)
                FROM community_expense_requests r
                INNER JOIN community_expense_groups g ON r.expense_group_id = g.expense_group_id
                WHERE r.head_parish_id = $headParishId
                AND r.request_status = 'Approved'
                AND g.account_id = $accountId
                AND r.request_datetime BETWEEN '$startDate' AND '$endDate'
            
                UNION ALL
            
                -- Group
                SELECT SUM(r.request_amount)
                FROM group_expense_requests r
                INNER JOIN group_expense_groups g ON r.expense_group_id = g.expense_group_id
                WHERE r.head_parish_id = $headParishId
                AND r.request_status = 'Approved'
                AND g.account_id = $accountId
                AND r.request_datetime BETWEEN '$startDate' AND '$endDate'
            ) AS combined_expenses
        ";


            $expenseResult = mysqli_query($conn, $expenseQuery);
            $totalExpense = 0;
            if ($expenseResult && mysqli_num_rows($expenseResult) > 0) {
                $expenseRow = mysqli_fetch_assoc($expenseResult);
                $totalExpense = $expenseRow['total_expense'] ?? 0;
            }

            // Store all values for this account
            $financials[] = [
                'account_id' => $accountId,
                'account_name' => $accountName,
                'opening_balance' => (float) $openingBalance,
                'total_revenue_collected' => (float) $totalRevenue,
                'total_approved_expenses' => (float) $totalExpense
            ];
        }

        mysqli_free_result($accountResult);
    }

    return $financials;
}

function getSubParishYearlyFinancials($headParishId, $year, $conn) {
    $results = [];

    // Get all sub parishes under this head parish
    $subParishQuery = "
        SELECT sub_parish_id, sub_parish_name 
        FROM sub_parishes 
        WHERE head_parish_id = $headParishId
    ";
    $subParishResult = mysqli_query($conn, $subParishQuery);

    if ($subParishResult) {
        while ($subParish = mysqli_fetch_assoc($subParishResult)) {
            $subParishId = $subParish['sub_parish_id'];
            $subParishName = $subParish['sub_parish_name'];

            // Get current year revenue
            $currentYearRevenue = 0;
            $revenueQueryCurrent = "
                SELECT SUM(revenue_amount) AS total 
                FROM sub_parish_revenues 
                WHERE head_parish_id = $headParishId 
                AND sub_parish_id = $subParishId 
                AND YEAR(revenue_date) = $year
            ";
            $revenueCurrentResult = mysqli_query($conn, $revenueQueryCurrent);
            if ($revenueCurrentResult) {
                $row = mysqli_fetch_assoc($revenueCurrentResult);
                $currentYearRevenue = $row['total'] ?? 0;
            }

            // Get previous year's closing balance as of 31 Dec
            $previousYear = $year - 1;
            $closingBalance = 0;
            $closingBalanceQuery = "
                SELECT SUM(closing_balance) AS total 
                FROM sub_parish_bank_account_closing_balances 
                WHERE head_parish_id = $headParishId 
                AND sub_parish_id = $subParishId 
                AND balance_date = '{$previousYear}-12-31'
            ";
            $closingBalanceResult = mysqli_query($conn, $closingBalanceQuery);
            if ($closingBalanceResult) {
                $row = mysqli_fetch_assoc($closingBalanceResult);
                $closingBalance = $row['total'] ?? 0;
            }

            // Get approved expenses for current year
            $totalApprovedExpenses = 0;
            $expenseQuery = "
                SELECT SUM(request_amount) AS total 
                FROM sub_parish_expense_requests 
                WHERE head_parish_id = $headParishId 
                AND sub_parish_id = $subParishId 
                AND request_status = 'Approved' 
                AND YEAR(request_datetime) = $year
            ";
            $expenseResult = mysqli_query($conn, $expenseQuery);
            if ($expenseResult) {
                $row = mysqli_fetch_assoc($expenseResult);
                $totalApprovedExpenses = $row['total'] ?? 0;
            }

            // Store the final data
            $results[] = [
                'sub_parish_id' => $subParishId,
                'sub_parish_name' => $subParishName,
                'current_year_revenue' => (float)$currentYearRevenue,
                'previous_year_revenue' => (float)$closingBalance,
                'total_approved_expenses' => (float)$totalApprovedExpenses
            ];
        }

        mysqli_free_result($subParishResult);
    }

    return $results;
}


function getSubParishYearlyFinancialsQuarterly($headParishId, $previousQuarterEndDate, $startDate, $endDate, $conn) {
    $results = [];

    // Get all sub parishes under this head parish
    $subParishQuery = "
        SELECT sub_parish_id, sub_parish_name 
        FROM sub_parishes 
        WHERE head_parish_id = $headParishId
    ";
    $subParishResult = mysqli_query($conn, $subParishQuery);

    if ($subParishResult) {
        while ($subParish = mysqli_fetch_assoc($subParishResult)) {
            $subParishId = $subParish['sub_parish_id'];
            $subParishName = $subParish['sub_parish_name'];

            // Get current year revenue
            $currentYearRevenue = 0;
            $revenueQueryCurrent = "
                SELECT SUM(revenue_amount) AS total 
                FROM sub_parish_revenues 
                WHERE head_parish_id = $headParishId 
                AND sub_parish_id = $subParishId 
                AND revenue_date BETWEEN '$startDate' AND '$endDate'
            ";
            $revenueCurrentResult = mysqli_query($conn, $revenueQueryCurrent);
            if ($revenueCurrentResult) {
                $row = mysqli_fetch_assoc($revenueCurrentResult);
                $currentYearRevenue = $row['total'] ?? 0;
            }

            // Get previous quarter's closing balance 
            $closingBalance = 0;
            $closingBalanceQuery = "
                SELECT SUM(closing_balance) AS total 
                FROM sub_parish_bank_account_closing_balances 
                WHERE head_parish_id = $headParishId 
                AND sub_parish_id = $subParishId 
                AND balance_date = '$previousQuarterEndDate'
            ";
            $closingBalanceResult = mysqli_query($conn, $closingBalanceQuery);
            if ($closingBalanceResult) {
                $row = mysqli_fetch_assoc($closingBalanceResult);
                $closingBalance = $row['total'] ?? 0;
            }

            // Get approved expenses for the period between start and end dates
            $totalApprovedExpenses = 0;
            $expenseQuery = "
                SELECT SUM(request_amount) AS total 
                FROM sub_parish_expense_requests 
                WHERE head_parish_id = $headParishId 
                AND sub_parish_id = $subParishId 
                AND request_status = 'Approved' 
                AND request_datetime BETWEEN '$startDate' AND '$endDate'
            ";
            $expenseResult = mysqli_query($conn, $expenseQuery);
            if ($expenseResult) {
                $row = mysqli_fetch_assoc($expenseResult);
                $totalApprovedExpenses = $row['total'] ?? 0;
            }

            // Store the final data
            $results[] = [
                'sub_parish_id' => $subParishId,
                'sub_parish_name' => $subParishName,
                'current_year_revenue' => (float)$currentYearRevenue,
                'previous_year_revenue' => (float)$closingBalance,
                'total_approved_expenses' => (float)$totalApprovedExpenses
            ];
        }

        mysqli_free_result($subParishResult);
    }

    return $results;
}


function getUnpaidDebtsByYear($headParishId, $year, $conn) {
    $results = [];

    $query = "
        SELECT 
            description AS debt_name,
            amount,
            date_debited,
            return_before_date,
            purpose
        FROM head_parish_debits
        WHERE 
            head_parish_id = $headParishId
            AND is_paid = 0
            AND YEAR(date_debited) = $year
    ";

    $result = mysqli_query($conn, $query);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $results[] = [
                'debt_name' => $row['debt_name'],
                'amount' => (float)$row['amount'],
                'date_debited' => $row['date_debited'],
                'return_before_date' => $row['return_before_date'],
                'purpose' => $row['purpose'],
            ];
        }
        mysqli_free_result($result);
    }

    return $results;
}

function getUnpaidDebtsByQuarter($headParishId, $startDate, $endDate, $conn) {
    $results = [];

    // Query to fetch unpaid debts for the specified quarter (between start and end date)
    $query = "
        SELECT 
            description AS debt_name,
            amount,
            date_debited,
            return_before_date,
            purpose
        FROM head_parish_debits
        WHERE 
            head_parish_id = $headParishId
            AND is_paid = 0
            AND date_debited BETWEEN '$startDate' AND '$endDate'
    ";

    $result = mysqli_query($conn, $query);

    if ($result) {
        while ($row = mysqli_fetch_assoc($result)) {
            $results[] = [
                'debt_name' => $row['debt_name'],
                'amount' => (float)$row['amount'],
                'date_debited' => $row['date_debited'],
                'return_before_date' => $row['return_before_date'],
                'purpose' => $row['purpose'],
            ];
        }
        mysqli_free_result($result);
    }

    return $results;
}


function getAccountNameById($accountId, $headParishId, $conn) {
    // Initialize the account name variable
    $accountName = '';

    // Prepare the SQL query to fetch the account name based on account_id and head_parish_id
    $query = "
        SELECT account_name
        FROM head_parish_bank_accounts
        WHERE account_id = ? AND head_parish_id = ?
    ";

    // Prepare the SQL statement
    if ($stmt = mysqli_prepare($conn, $query)) {
        // Bind the parameters to the statement
        mysqli_stmt_bind_param($stmt, "ii", $accountId, $headParishId);
        
        // Execute the query
        mysqli_stmt_execute($stmt);
        
        // Get the result
        $result = mysqli_stmt_get_result($stmt);
        
        // Check if a row was returned
        if ($row = mysqli_fetch_assoc($result)) {
            // Fetch the account name and capitalize it
            $accountName = ucwords(strtolower($row['account_name']));
        }
        
        // Close the prepared statement
        mysqli_stmt_close($stmt);
    }

    // Return the capitalized account name
    return strtoupper($accountName);
}

function getHeadParishRevenueTargetAmountBySubParish($headParishId, $subParishId, $year, $conn) {
    // Prepare the SQL query
    $sql = "
        SELECT revenue_target_amount
        FROM head_parish_annual_revenue_distribution
        WHERE head_parish_id = ? 
        AND sub_parish_id = ? 
        AND YEAR(revenue_target_start_date) <= ? 
        AND YEAR(revenue_target_end_date) >= ?
    ";
    
    // Prepare the statement
    if ($stmt = mysqli_prepare($conn, $sql)) {
        
        // Bind the parameters
        mysqli_stmt_bind_param($stmt, "iiii", $headParishId, $subParishId, $year, $year);
        
        // Execute the statement
        mysqli_stmt_execute($stmt);
        
        // Bind the result variable
        mysqli_stmt_bind_result($stmt, $revenueTargetAmount);
        
        // Fetch the result
        if (mysqli_stmt_fetch($stmt)) {
            // Return the revenue target amount
            mysqli_stmt_close($stmt);
            return $revenueTargetAmount;
        } else {
            // No matching record found
            mysqli_stmt_close($stmt);
            return null;
        }
    } else {
        // Query preparation failed
        return null;
    }
}



function getOverBudgetExpenseNames($headParishId, $year, $conn) {
    $expenses = [];

    // 1. Get all expense names with budget
    $budgetQuery = "
        SELECT 
            en.expense_name_id,
            en.expense_name,
            SUM(b.budgeted_amount) AS total_budget
        FROM head_parish_expense_names en
        LEFT JOIN head_parish_expense_budgets b 
            ON en.expense_name_id = b.expense_name_id 
            AND b.head_parish_id = $headParishId 
            AND YEAR(b.start_date) <= $year AND YEAR(b.end_date) >= $year
        GROUP BY en.expense_name_id
    ";
    $budgetResult = mysqli_query($conn, $budgetQuery);
    while ($row = mysqli_fetch_assoc($budgetResult)) {
        $id = $row['expense_name_id'];
        $expenses[$id] = [
            'expense_id' => $id,
            'expense_name' => $row['expense_name'],
            'budget_amount' => (float)($row['total_budget'] ?? 0),
            'total_expense' => 0,
        ];
    }

    // 2. Get total expenses
    $expenseQuery = "
        SELECT 
            expense_name_id,
            SUM(request_amount) AS total_expense
        FROM head_parish_expense_requests
        WHERE head_parish_id = $headParishId
        AND request_status = 'Approved'
        AND YEAR(request_datetime) = $year
        GROUP BY expense_name_id
    ";
    $expenseResult = mysqli_query($conn, $expenseQuery);
    while ($row = mysqli_fetch_assoc($expenseResult)) {
        $id = $row['expense_name_id'];
        if (!isset($expenses[$id])) {
            $expenses[$id] = [
                'expense_id' => $id,
                'expense_name' => '', // we’ll ignore this if not needed
                'budget_amount' => 0,
                'total_expense' => 0,
            ];
        }
        $expenses[$id]['total_expense'] = (float)$row['total_expense'];
    }

    // 3. Final Filtering: include if either budget > 0 or expense > 0
    $final = [];
    foreach ($expenses as $e) {
        if (($e['budget_amount'] > 0 || $e['total_expense'] > 0) && $e['total_expense'] > $e['budget_amount']) {
            $e['balance'] = $e['budget_amount'] - $e['total_expense'];
            $e['annual_balance'] = $e['balance'];
            // Avoid division by zero by checking if budget_amount is greater than 0
            $overBudgetPercentage = 0;
            if ($e['budget_amount'] > 0) {
                $overBudgetPercentage = (($e['total_expense'] - $e['budget_amount']) / $e['budget_amount']) * 100;
            }else{
                $overBudgetPercentage = 100;
            }
             $e['over_budget_percentage'] = $overBudgetPercentage;
            // Determine impact level based on the over-budget percentage
            if ($overBudgetPercentage >= 50) {
                $e['impact'] = 'Critical';
            } elseif ($overBudgetPercentage >= 30) {
                $e['impact'] = 'Severe Impact';
            } elseif ($overBudgetPercentage >= 10) {
                $e['impact'] = 'Over Budget – Action Needed';
            } elseif ($overBudgetPercentage > 0) {
                $e['impact'] = 'Budget Breach';
            }elseif ($overBudgetPercentage < 0) {
                 $e['impact'] = 'Within Annual Budget';
            }
            else {
                $e['impact'] = 'Balance';
            }
            $final[] = $e;
        }
    }

    return $final;
}


function getOverBudgetExpenseNamesQuarterly($headParishId, $year, $startDate, $endDate, $conn) {
    $expenses = [];

    // 1. Get all expense names with budget
    $budgetQuery = "
        SELECT 
            en.expense_name_id,
            en.expense_name,
            SUM(b.budgeted_amount) AS total_budget
        FROM head_parish_expense_names en
        LEFT JOIN head_parish_expense_budgets b 
            ON en.expense_name_id = b.expense_name_id 
            AND b.head_parish_id = $headParishId 
            AND YEAR(b.start_date) <= $year 
            AND YEAR(b.end_date) >= $year
        GROUP BY en.expense_name_id
    ";
    $budgetResult = mysqli_query($conn, $budgetQuery);
    while ($row = mysqli_fetch_assoc($budgetResult)) {
        $id = $row['expense_name_id'];
        $expenses[$id] = [
            'expense_id' => $id,
            'expense_name' => $row['expense_name'],
            'budget_amount' => (float)($row['total_budget'] ?? 0),
            'total_expense' => 0,
            'annual_expense' => 0,
        ];
    }

    // 2. Quarterly expenses (between $startDate and $endDate)
    $expenseQuery = "
        SELECT 
            expense_name_id,
            SUM(request_amount) AS total_expense
        FROM head_parish_expense_requests
        WHERE head_parish_id = $headParishId
        AND request_status = 'Approved'
        AND DATE(request_datetime) BETWEEN '$startDate' AND '$endDate'
        GROUP BY expense_name_id
    ";
    $expenseResult = mysqli_query($conn, $expenseQuery);
    while ($row = mysqli_fetch_assoc($expenseResult)) {
        $id = $row['expense_name_id'];
        if (!isset($expenses[$id])) {
            $expenses[$id] = [
                'expense_id' => $id,
                'expense_name' => '',
                'budget_amount' => 0,
                'total_expense' => 0,
                'annual_expense' => 0,
            ];
        }
        $expenses[$id]['total_expense'] = (float)$row['total_expense'];
    }

    // 3. Annual expenses (from Jan 01 of $year up to $endDate)
    $annualStart = $year . "-01-01";
    $annualExpenseQuery = "
        SELECT 
            expense_name_id,
            SUM(request_amount) AS total_expense
        FROM head_parish_expense_requests
        WHERE head_parish_id = $headParishId
        AND request_status = 'Approved'
        AND DATE(request_datetime) BETWEEN '$annualStart' AND '$endDate'
        GROUP BY expense_name_id
    ";
    $annualResult = mysqli_query($conn, $annualExpenseQuery);
    while ($row = mysqli_fetch_assoc($annualResult)) {
        $id = $row['expense_name_id'];
        if (!isset($expenses[$id])) {
            $expenses[$id] = [
                'expense_id' => $id,
                'expense_name' => '',
                'budget_amount' => 0,
                'total_expense' => 0,
                'annual_expense' => 0,
            ];
        }
        $expenses[$id]['annual_expense'] = (float)$row['total_expense'];
    }

    // 4. Final filtering & calculations
    $final = [];
    foreach ($expenses as $e) {
        if (($e['budget_amount'] > 0 || $e['total_expense'] > 0) && $e['total_expense'] > ($e['budget_amount'] / 4)) {
            $e['annual_balance'] = $e['budget_amount'] - $e['annual_expense'];
            $e['balance'] = 0;
            $overBudgetPercentage = 0;

            if ($e['budget_amount'] > 0) {
                $overBudgetPercentage = (($e['annual_expense'] - $e['budget_amount']) / $e['budget_amount']) * 100;
                $quarterBudget = $e['budget_amount'] / 4;
                $e['balance'] = $quarterBudget - $e['total_expense'];
                $e['budget_amount'] = $quarterBudget; // keep quarterly display
            } else {
                $e['balance'] = -$e['total_expense'];
                $overBudgetPercentage = 100;
            }

            $e['over_budget_percentage'] = $overBudgetPercentage;

            // Impact levels
            if ($overBudgetPercentage >= 50) {
                $e['impact'] = 'Critical';
            } elseif ($overBudgetPercentage >= 30) {
                $e['impact'] = 'Severe Impact';
            } elseif ($overBudgetPercentage >= 10) {
                $e['impact'] = 'Over Budget – Action Needed';
            } elseif ($overBudgetPercentage > 0) {
                $e['impact'] = 'Budget Breach';
            } elseif ($overBudgetPercentage <= 0) {
                $e['impact'] = 'Within Annual Budget';
            } else {
                $e['impact'] = 'Balance';
            }

            $final[] = $e;
        }
    }

    return $final;
}


function getHeadParishHarambeeClasses($head_parish_id, $conn) {
    $sql = "SELECT 
                harambee_class_id, 
                class_name, 
                amount_min, 
                amount_max 
            FROM head_parish_harambee_classes 
            WHERE head_parish_id = ?";
    
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return [];
    }

    $stmt->bind_param("i", $head_parish_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $classes = [];

    while ($row = $result->fetch_assoc()) {
        $classes[] = [
            'harambee_class_id' => $row['harambee_class_id'],
            'class_name' => $row['class_name'],
            'amount_min' => $row['amount_min'],
            'amount_max' => (!is_null($row['amount_max']) && $row['amount_max'] != 0)
                ? $row['amount_max']
                : null
        ];
    }

    $stmt->close();
    return $classes;
}

// function groupMembersByClass($all_member_details_array, $harambee_classes) {
//     $grouped = [];

//     // Add a NO TARGET class
//     $noTargetClass = [
//         'harambee_class_id' => 'no-target',
//         'class_name' => 'NO TARGET',
//         'amount_min' => 0,
//         'amount_max' => 0,
//         'members' => []
//     ];

//     // Initialize groups for each class
//     foreach ($harambee_classes as $class) {
//         $grouped[$class['harambee_class_id']] = [
//             'harambee_class_id' => $class['harambee_class_id'],
//             'class_name' => $class['class_name'],
//             'amount_min' => $class['amount_min'],
//             'amount_max' => $class['amount_max'],
//             'members' => []
//         ];
//     }

//     // Group members
//     foreach ($all_member_details_array as $member) {
//         $target = (float)$member['target'];

//         if ($target <= 0) {
//             $noTargetClass['members'][] = $member;
//             continue;
//         }

//         $matched = false;

//         foreach ($harambee_classes as $class) {
//             $min = (float)$class['amount_min'];
//             $max = $class['amount_max'] !== null ? (float)$class['amount_max'] : null;

//             if ($target >= $min && ($max === null || $target <= $max)) {
//                 $grouped[$class['harambee_class_id']]['members'][] = $member;
//                 $matched = true;
//                 break;
//             }
//         }

//         if (!$matched) {
//             // If no matching class is found, fallback to NO TARGET
//             $noTargetClass['members'][] = $member;
//         }
//     }

//     // Only add NO TARGET class if it has members
//     if (!empty($noTargetClass['members'])) {
//         $grouped[$noTargetClass['harambee_class_id']] = $noTargetClass;
//     }

//     return $grouped;
// }

function groupMembersByClass($all_member_details_array, $harambee_classes) {
    $grouped = [];

    // Add a NO TARGET class
    $noTargetClass = [
        'harambee_class_id' => 'no-target',
        'class_name' => 'NO TARGET',
        'amount_min' => 0,
        'amount_max' => 0,
        'members' => [],
        'summary' => [
            'total_members' => 0,
            'total_target' => 0,
            'completed' => ['count' => 0, 'amount' => 0],
            'on_progress' => ['count' => 0, 'amount' => 0, 'balance' => 0],
            'not_contributed' => ['count' => 0, 'amount' => 0],
        ]
    ];
    
    $wageniClass = [
        'harambee_class_id' => 'wageni',
        'class_name' => 'Wageni',
        'amount_min' => 0,
        'amount_max' => 0,
        'members' => [],
        'summary' => [
            'total_members' => 0,
            'total_target' => 0,
            'completed' => ['count' => 0, 'amount' => 0],
            'on_progress' => ['count' => 0, 'amount' => 0, 'balance' => 0],
            'not_contributed' => ['count' => 0, 'amount' => 0],
        ]
    ];

    // Initialize groups for each class
    foreach ($harambee_classes as $class) {
        $grouped[$class['harambee_class_id']] = [
            'harambee_class_id' => $class['harambee_class_id'],
            'class_name' => $class['class_name'],
            'amount_min' => $class['amount_min'],
            'amount_max' => $class['amount_max'],
            'members' => [],
            'summary' => [
                'total_members' => 0,
                'total_target' => 0,
                'completed' => ['count' => 0, 'amount' => 0],
                'on_progress' => ['count' => 0, 'amount' => 0, 'balance' => 0],
                'not_contributed' => ['count' => 0, 'amount' => 0],
            ]
        ];
    }

    // Group members and build summaries
    foreach ($all_member_details_array as $member) {
        $target = (float)$member['target'];
        $contribution = (float)$member['contribution'];
        $balance = (float)$member['balance'];
        $category = $member['category'];
        $memberType = $member['member_type'];
        
        // Group Wageni first
        if (strtolower($memberType) === 'mgeni') {
            $wageniClass['members'][] = $member;
            $wageniClass['summary']['total_members']++;
            $wageniClass['summary']['total_target'] += $target;
    
            if ($category === 'completed') {
                $wageniClass['summary']['completed']['count']++;
                $wageniClass['summary']['completed']['amount'] += $contribution;
            } elseif ($category === 'on_progress') {
                $wageniClass['summary']['on_progress']['count']++;
                $wageniClass['summary']['on_progress']['amount'] += $contribution;
                $wageniClass['summary']['on_progress']['balance'] += $balance;
            } elseif ($category === 'not_contributed') {
                $wageniClass['summary']['not_contributed']['count']++;
                $wageniClass['summary']['not_contributed']['amount'] += $target;
            }
    
            continue; // Skip other classification
        }
        
        // Handle NO TARGET separately
        if ($target <= 0) {
            $noTargetClass['members'][] = $member;
            $noTargetClass['summary']['total_members']++;
            if ($category === 'completed') {
                $noTargetClass['summary']['completed']['count']++;
                $noTargetClass['summary']['completed']['amount'] += $contribution;
            }
            continue;
        }

        // Match the member to a class
        $matched = false;
        foreach ($harambee_classes as $class) {
            $min = (float)$class['amount_min'];
            $max = $class['amount_max'] !== null ? (float)$class['amount_max'] : null;

            if ($target >= $min && ($max === null || $target <= $max)) {
                $group =& $grouped[$class['harambee_class_id']];
                $group['members'][] = $member;
                $group['summary']['total_members']++;
                $group['summary']['total_target'] += $target;

                if ($category === 'completed') {
                    $group['summary']['completed']['count']++;
                    $group['summary']['completed']['amount'] += $contribution;
                } elseif ($category === 'on_progress') {
                    $group['summary']['on_progress']['count']++;
                    $group['summary']['on_progress']['amount'] += $contribution;
                    $group['summary']['on_progress']['balance'] += $balance;
                } elseif ($category === 'not_contributed') {
                    $group['summary']['not_contributed']['count']++;
                    $group['summary']['not_contributed']['amount'] += $target;
                }

                $matched = true;
                break;
            }
        }

        // If no class matched (which shouldn't happen), treat as NO TARGET
        if (!$matched) {
            $noTargetClass['members'][] = $member;
            $noTargetClass['summary']['total_members']++;
        }
    }

    // Only add NO TARGET class if it has members
    if (!empty($noTargetClass['members'])) {
        $grouped[$noTargetClass['harambee_class_id']] = $noTargetClass;
    }
    
    $grouped[$wageniClass['harambee_class_id']] = $wageniClass;


    return $grouped;
}

function renderAdminRoleSelectOptions($name = 'head_parish_admin_role', $id = 'adminRoleHP') {
    echo '<select class="form-select" id="' . htmlspecialchars($id) . '" name="' . htmlspecialchars($name) . '">
              <option value="">Select Role</option>
              <option value="pastor">Mchungaji Kiongozi</option>
              <option value="evangelist">Mwinjilisti</option>
              <option value="secretary">Katibu</option>
              <option value="accountant">Mhasibu</option>
              <option value="chairperson">M/Kiti</option>
              <option value="clerk">Kalani</option>
          </select>';
}

function getRevenueStreamIdByProgram($conn, $head_parish_id, $program) {
    $query = "SELECT revenue_stream_id FROM program_revenue_map WHERE head_parish_id = ? AND program = ?";
    $stmt = $conn->prepare($query);
    if (!$stmt) return null;

    $stmt->bind_param("is", $head_parish_id, $program);
    $stmt->execute();
    $stmt->bind_result($revenue_stream_id);

    if ($stmt->fetch()) {
        $stmt->close();
        return $revenue_stream_id;
    } else {
        $stmt->close();
        return null; // Not mapped
    }
}

function sendExpenseRequestSms($conn, $head_parish_id, $role,  $target, $description = null) {
    // Map input role to DB value
    $db_role = $role === 'chairperson' ? 'admin' : $role;

    // Step 1: Get SMS API credentials
    $smsInfo = getHeadParishSmsInfo($conn, $head_parish_id);
    if (!$smsInfo) {
        error_log("No SMS credentials for head parish ID $head_parish_id");
        return false;
    }
    
    // If description provided, trim and ensure reasonable length
    $descText = "";
    if (!empty($description)) {
        $cleanDesc = trim($description);
        // Limit to 60 chars in SMS
        if (mb_strlen($cleanDesc) > 60) {
            $cleanDesc = mb_substr($cleanDesc, 0, 57) . "...";
        }
        $descText = $cleanDesc;
    }

    // Determine expense operation level text
    $levelText = "";
    switch (strtolower($target)) {
        case 'head-parish':
            $levelText = "Ngazi ya USHARIKA";
            break;
    
        case 'sub-parish':
            $levelText = "Ngazi ya MITAA";
            break;
    
        case 'community':
            $levelText = "Ngazi ya JUMUIYA";
            break;
    
        case 'group':
        case 'groups':
            $levelText = "Ngazi ya IDARA NA VIKUNDI";
            break;
    
        default:
            $levelText = "Ngazi haijabainishwa";
            break;
    }

    // Prepare recipients based on role
    $recipients = [];
    if ($db_role === 'pastor') {
        // When pastor approves, notify both accountant and chairperson (admin)
        $recipients = ['accountant', 'admin'];
    } else {
        switch ($db_role) {
            case 'accountant':
                $recipients = ['admin'];        // Accountants notify Chairperson (DB role 'admin')
                break;
            case 'admin':
                $recipients = ['pastor'];       // Chairperson notifies Pastor
                break;
            default:
                error_log("Invalid DB ROLE");
                return false;
        }
    }

    // Normalize and send SMS to each recipient
    foreach ($recipients as $target_admin_role) {

        // Step 2: Get recipient info
        $admin = getAdminPhoneAndFirstNameByRole($conn, $head_parish_id, $target_admin_role);
        $phone = $admin['phone'];
        $first_name = $admin['first_name'];

        if (empty($phone)) {
            error_log("No phone found for role '$target_admin_role' in head parish ID $head_parish_id");
            continue;
        }

        // Normalize phone to international format
        if (substr($phone, 0, 1) === '0') {
            $phone = '255' . substr($phone, 1);
        }

        // Step 3: Build SMS message
        switch ($db_role) {
            case 'accountant':
                $message = "Shalom M/Kiti wa Mali,\nMaombi mapya ya matumizi yamewasilishwa katika $levelText. Tafadhali yafanyie kazi kwa wakati.\nMratibu wa Mali";
                break;

            case 'admin':
                $message = "Shalom Mchungaji Kiongozi,\nMaombi ya matumizi kutoka $levelText yameidhinishwa na yamewasilishwa kwako kwa utekelezaji.\nM/Kiti wa Mali";
                break;

            case 'pastor':
                if ($target_admin_role === 'accountant') {
                    $message = "Shalom Mratibu wa Mali,\nMaombi ya matumizi kutoka $levelText yamefanyiwa kazi. Endelea na hatua zinazofuata.\nMchungaji Kiongozi";
                } else {
                    // admin = Chairperson
                    $message = "Shalom M/Kiti wa Mali,\nMaombi ya matumizi kutoka $levelText yameidhinishwa. Tafadhali yafanyie kazi kwa wakati.\nMchungaji Kiongozi";
                }
                break;

            default:
                error_log("Unsupported role '$db_role'");
                continue 2;
        }

        // Step 4: Send SMS using quick send
        $smsClient = new SewmrSMSClient($smsInfo['api_token'], $smsInfo['sender_id']);
        $response = $smsClient->sendQuickSMS(null, $message, [$phone]);
        
        if (!isset($response['success']) || $response['success'] !== true) {
            $errorMessage = $response['message'] ?? "Unknown error";
            error_log("Failed to send SMS to {$phone}: $errorMessage");
        }

    }

    return true;
}

function getBudgetAndExpenseSummaryByDate($conn, $date, $head_parish_id, $expense_name_id, $target, $sub_parish_id = null, $community_id = null, $group_id = null) {
    $year = date('Y', strtotime($date));

    // Determine quarter based on month
    $month = date('n', strtotime($date));
    if ($month >= 1 && $month <= 3) {
        $quarter = 'Q1';
        $quarter_start = "$year-01-01";
        $quarter_end = "$year-03-31";
    } elseif ($month >= 4 && $month <= 6) {
        $quarter = 'Q2';
        $quarter_start = "$year-04-01";
        $quarter_end = "$year-06-30";
    } elseif ($month >= 7 && $month <= 9) {
        $quarter = 'Q3';
        $quarter_start = "$year-07-01";
        $quarter_end = "$year-09-30";
    } else {
        $quarter = 'Q4';
        $quarter_start = "$year-10-01";
        $quarter_end = "$year-12-31";
    }

    // Define tables
    $budget_table = '';
    $expense_table = '';
    $id_column = '';
    switch ($target) {
        case 'head-parish':
            $budget_table = 'head_parish_expense_budgets';
            $expense_table = 'head_parish_expense_requests';
            $id_column = 'head_parish_id';
            break;
        case 'sub-parish':
            $budget_table = 'sub_parish_expense_budgets';
            $expense_table = 'sub_parish_expense_requests';
            $id_column = 'sub_parish_id';
            break;
        case 'community':
            $budget_table = 'community_expense_budgets';
            $expense_table = 'community_expense_requests';
            $id_column = 'community_id';
            break;
        case 'group':
        case 'groups':
            $budget_table = 'group_expense_budgets';
            $expense_table = 'group_expense_requests';
            $id_column = 'group_id';
            break;
        default:
            return ["error" => "Invalid target"];
    }

    // Build WHERE clause
    $where = "$budget_table.head_parish_id = ? AND $budget_table.expense_name_id = ? AND YEAR(start_date) = ?";
    $params = [$head_parish_id, $expense_name_id, $year];
    $types = "iii";

    if ($target === 'sub-parish') {
        $where .= " AND sub_parish_id = ?";
        $params[] = $sub_parish_id;
        $types .= "i";
    } elseif ($target === 'community') {
        $where .= " AND sub_parish_id = ? AND community_id = ?";
        $params[] = $sub_parish_id;
        $params[] = $community_id;
        $types .= "ii";
    } elseif ($target === 'group') {
        $where .= " AND group_id = ?";
        $params[] = $group_id;
        $types .= "i";
    }

    // Fetch annual budget
    $stmt = $conn->prepare("SELECT SUM(budgeted_amount) AS total_budget FROM $budget_table WHERE $where");
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $budget_result = $stmt->get_result()->fetch_assoc();
    $annual_budget = floatval($budget_result['total_budget']);

    // Fetch annual approved expenses
    $stmt = $conn->prepare("SELECT SUM(request_amount) AS total_expense FROM $expense_table WHERE expense_name_id = ? AND request_status = 'Approved' AND $id_column = ? AND YEAR(request_datetime) = ?");
    $stmt->bind_param("iii", $expense_name_id, ${$id_column}, $year);
    $stmt->execute();
    $expense_result = $stmt->get_result()->fetch_assoc();
    $annual_expense = floatval($expense_result['total_expense']);

    // Fetch quarterly approved expenses
    $stmt = $conn->prepare("SELECT SUM(request_amount) AS quarter_expense FROM $expense_table WHERE expense_name_id = ? AND request_status = 'Approved' AND $id_column = ? AND request_datetime BETWEEN ? AND ?");
    $stmt->bind_param("iiss", $expense_name_id, ${$id_column}, $quarter_start, $quarter_end);
    $stmt->execute();
    $quarter_result = $stmt->get_result()->fetch_assoc();
    $quarter_expense = floatval($quarter_result['quarter_expense']);
    
     // Fetch quarterly pending expenses (new addition)
    $stmt = $conn->prepare("SELECT SUM(request_amount) AS quarter_pending FROM $expense_table WHERE expense_name_id = ? AND request_status = 'Pending' AND $id_column = ? AND request_datetime BETWEEN ? AND ?");
    $stmt->bind_param("iiss", $expense_name_id, ${$id_column}, $quarter_start, $quarter_end);
    $stmt->execute();
    $pending_result = $stmt->get_result()->fetch_assoc();
    $quarter_pending = floatval($pending_result['quarter_pending']);

    // Compute derived values
    $quarter_budget = $annual_budget / 4;
    $annual_balance = $annual_budget - $annual_expense;
    $quarter_balance = $quarter_budget - $quarter_expense;

    return [
        "year" => $year,
        "quarter" => $quarter,
        "annual_budget" => $annual_budget,
        "quarter_budget" => $quarter_budget,
        "annual_expense" => $annual_expense,
        "quarter_expense" => $quarter_expense,
        "quarter_pending" => $quarter_pending,
        "annual_balance" => $annual_balance,
        "quarter_balance" => $quarter_balance
    ];
}


function getExpenseIdsFromRequestId($conn, $request_id, $target) {
    $table = '';
    $fields = '';
    switch ($target) {
        case 'sub-parish':
            $table = 'sub_parish_expense_requests';
            $fields = 'sub_parish_id, expense_name_id, DATE(request_datetime) AS request_date';
            break;
        case 'community':
            $table = 'community_expense_requests';
            $fields = 'sub_parish_id, community_id, expense_name_id, DATE(request_datetime) AS request_date';
            break;
        case 'group':
        case 'groups':
            $table = 'group_expense_requests';
            $fields = 'group_id, expense_name_id, DATE(request_datetime) AS request_date';
            break;
        default: // head-parish
            $table = 'head_parish_expense_requests';
            $fields = 'head_parish_id, expense_name_id, DATE(request_datetime) AS request_date';
            break;
    }

    $stmt = $conn->prepare("SELECT $fields FROM $table WHERE request_id = ?");
    if (!$stmt) {
        return ["error" => "Prepare failed: " . $conn->error];
    }

    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 0) {
        return ["error" => "Request ID not found"];
    }

    $row = $result->fetch_assoc();
    $stmt->close();

    return $row;
}

function getSignaturePaths($conn, $head_parish_id) {
    $roles = ['accountant', 'pastor', 'admin'];
    $signatures = [];

    foreach ($roles as $role) {
        $sql = "SELECT head_parish_admin_fullname, signature_path 
                FROM head_parish_admins 
                WHERE head_parish_id = ? AND head_parish_admin_role = ? AND signature_path IS NOT NULL 
                LIMIT 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $head_parish_id, $role);
        $stmt->execute();
        $stmt->bind_result($fullname, $signature_path);
        
        if ($stmt->fetch()) {
            $signatures[$role] = [
                'name' => $fullname,
                'path' => $_SERVER['DOCUMENT_ROOT'] . '/uploads/signatures/' . $signature_path
            ];
        } else {
            $signatures[$role] = [
                'name' => null,
                'path' => null
            ];
        }
        $stmt->close();
    }

    return $signatures;
}


function validateExpenseGroupsAccount($conn, $target, $head_parish_id, array $expenseGroupIds) {
    if (empty($expenseGroupIds)) {
        return [false, "No expense groups provided"];
    }

    $head_parish_id = intval($head_parish_id);
    $expenseGroupIdsStr = implode(',', array_map('intval', $expenseGroupIds));
    $accountQuery = "";

    switch ($target) {
        case 'head-parish':
            $accountQuery = "SELECT DISTINCT account_id FROM head_parish_expense_groups WHERE head_parish_id = $head_parish_id AND expense_group_id IN ($expenseGroupIdsStr)";
            break;
        case 'sub-parish':
            $accountQuery = "SELECT DISTINCT account_id FROM sub_parish_expense_groups WHERE head_parish_id = $head_parish_id AND expense_group_id IN ($expenseGroupIdsStr)";
            break;
        case 'community':
            $accountQuery = "SELECT DISTINCT account_id FROM community_expense_groups WHERE head_parish_id = $head_parish_id AND expense_group_id IN ($expenseGroupIdsStr)";
            break;
        case 'group':
        case 'groups':
            $accountQuery = "SELECT DISTINCT account_id FROM group_expense_groups WHERE head_parish_id = $head_parish_id AND expense_group_id IN ($expenseGroupIdsStr)";
            break;
        default:
            return [false, "Unsupported target"];
    }

    $result = $conn->query($accountQuery);
    if (!$result) {
        return [false, "Database query failed: " . $conn->error];
    }

    $accountIds = [];
    while ($row = $result->fetch_assoc()) {
        $accountIds[] = $row['account_id'];
    }
    $accountIds = array_unique($accountIds);

    if (count($accountIds) !== 1) {
        return [false, "All expense groups must belong to the same bank account"];
    }
    
    return [true, null, $accountIds[0]];
}


function getExpenseStatsByRequest($conn, $request_id, $target) {
    $map = [
        'head-parish' => [
            'req_table'   => 'head_parish_expense_requests',
            'bud_table'   => 'head_parish_expense_budgets',
            'id_cols'     => ['head_parish_id'],
            'name_table'  => 'head_parish_expense_names',
            'extra_where' => ''
        ],
        'sub-parish' => [
            'req_table'   => 'sub_parish_expense_requests',
            'bud_table'   => 'sub_parish_expense_budgets',
            'id_cols'     => ['head_parish_id','sub_parish_id'],
            'name_table'  => 'sub_parish_expense_names',
            'extra_where' => 'AND sub_parish_id = ?'
        ],
        'community' => [
            'req_table'   => 'community_expense_requests',
            'bud_table'   => 'community_expense_budgets',
            'id_cols'     => ['head_parish_id','sub_parish_id','community_id'],
            'name_table'  => 'community_expense_names',
            'extra_where' => 'AND sub_parish_id = ? AND community_id = ?'
        ],
        'group' => [
            'req_table'   => 'group_expense_requests',
            'bud_table'   => 'group_expense_budgets',
            'id_cols'     => ['head_parish_id','group_id'],
            'name_table'  => 'group_expense_names',
            'extra_where' => 'AND group_id = ?'
        ]
    ];

    if (!isset($map[$target])) {
        return null;
    }
    $cfg = $map[$target];

    // Fetch the expense request
    $sqlReq = "SELECT * FROM {$cfg['req_table']} WHERE request_id = ?";
    $st = $conn->prepare($sqlReq);
    $st->bind_param('i', $request_id);
    $st->execute();
    $req = $st->get_result()->fetch_assoc();
    if (!$req) return null;

    $requestDate = $req['request_datetime'];
    $year = (int) date('Y', strtotime($requestDate));
    $expNameId = (int) $req['expense_name_id'];

    $ids = [];
    foreach ($cfg['id_cols'] as $col) {
        if (!isset($req[$col])) return null;
        $ids[] = $req[$col];
    }

    // Get expense name
    $sqlName = "SELECT expense_name FROM {$cfg['name_table']} WHERE expense_name_id = ?";
    $sn = $conn->prepare($sqlName);
    $sn->bind_param('i', $expNameId);
    $sn->execute();
    $nameRow = $sn->get_result()->fetch_assoc();
    $expenseName = $nameRow['expense_name'] ?? 'Unknown';

    // Get annual budget
    $baseBudSql = "SELECT budgeted_amount FROM {$cfg['bud_table']} 
                   WHERE head_parish_id = ? AND expense_name_id = ? 
                     AND YEAR(start_date) <= ? AND YEAR(end_date) >= ? 
                     {$cfg['extra_where']} LIMIT 1";
    $bb = $conn->prepare($baseBudSql);
    $types = 'iiii' . str_repeat('i', count($ids) - 1);
    $bb->bind_param($types, ...array_merge([$ids[0], $expNameId, $year, $year], array_slice($ids,1)));
    $bb->execute();
    $bud = $bb->get_result()->fetch_assoc();
    $annualBudget = (float) ($bud['budgeted_amount'] ?? 0);
    $quarterlyBudget = $target === 'head-parish' ? $annualBudget / 4 : $annualBudget;

    // Determine quarter for the request date
    $month = (int) date('m', strtotime($requestDate));
    if ($month >= 1 && $month <= 3) {
        $currentQuarter = 'Q1';
        $quarterStart = "$year-01-01";
        $quarterEnd = "$year-03-31";
    } elseif ($month >= 4 && $month <= 6) {
        $currentQuarter = 'Q2';
        $quarterStart = "$year-04-01";
        $quarterEnd = "$year-06-30";
    } elseif ($month >= 7 && $month <= 9) {
        $currentQuarter = 'Q3';
        $quarterStart = "$year-07-01";
        $quarterEnd = "$year-09-30";
    } else {
        $currentQuarter = 'Q4';
        $quarterStart = "$year-10-01";
        $quarterEnd = "$year-12-31";
    }

    // Get total approved expenses for the current quarter
    $qSql = "SELECT SUM(request_amount) AS total FROM {$cfg['req_table']} 
             WHERE expense_name_id = ? AND request_status = 'Approved' 
               AND request_datetime BETWEEN ? AND ? 
               AND head_parish_id = ? {$cfg['extra_where']}";
    $qst = $conn->prepare($qSql);
    $bindParams = array_merge([$expNameId, $quarterStart, $quarterEnd, $ids[0]], array_slice($ids,1));
    $qst->bind_param(str_repeat('s', count($bindParams)), ...$bindParams);
    $qst->execute();
    $r = $qst->get_result()->fetch_assoc();
    $currentQuarterApproved = (float) ($r['total'] ?? 0);

    // Get total approved expenses for the whole year
    $yearStart = "$year-01-01";
    $yearEnd = "$year-12-31";
    $aSql = "SELECT SUM(request_amount) AS total FROM {$cfg['req_table']} 
             WHERE expense_name_id = ? AND request_status = 'Approved' 
               AND request_datetime BETWEEN ? AND ? 
               AND head_parish_id = ? {$cfg['extra_where']}";
    $ast = $conn->prepare($aSql);
    $aBindParams = array_merge([$expNameId, $yearStart, $yearEnd, $ids[0]], array_slice($ids,1));
    $ast->bind_param(str_repeat('s', count($aBindParams)), ...$aBindParams);
    $ast->execute();
    $aRes = $ast->get_result()->fetch_assoc();
    $totalApprovedAnnual = (float) ($aRes['total'] ?? 0);

    $currentQuarterBalance = $quarterlyBudget - $currentQuarterApproved;
    $annualBalance = $annualBudget - $totalApprovedAnnual;

    return [
        'expense_name'             => $expenseName,
        'annual_budget'            => $annualBudget,
        'quarterly_budget'         => $quarterlyBudget,
        'total_approved_annual'    => $totalApprovedAnnual,
        'current_quarter'          => $currentQuarter,
        'current_quarter_budget'   => $quarterlyBudget,
        'current_quarter_approved' => $currentQuarterApproved,
        'current_quarter_balance'  => $currentQuarterBalance,
        'annual_balance'           => $annualBalance,
        'year'                     => $year,
        'request_id'               => $request_id,
    ];
}

function getGroupedRequestDetails(mysqli $conn, int $grouped_request_id, string $target): ?array {
    $tableMap = [
        'head-parish' => 'head_parish_grouped_expense_requests',
        'sub-parish'  => 'sub_parish_grouped_expense_requests',
        'community'   => 'community_grouped_expense_requests',
        'group'       => 'group_grouped_expense_requests',
    ];

    if (!array_key_exists($target, $tableMap)) {
        return null;
    }

    $table = $tableMap[$target];

    $sql = "
        SELECT 
            t.description, 
            t.submission_datetime, 
            a.head_parish_admin_fullname
        FROM $table AS t
        LEFT JOIN head_parish_admins AS a ON t.recorded_by = a.head_parish_admin_id
        WHERE t.grouped_request_id = ?
        LIMIT 1
    ";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('i', $grouped_request_id);
    $stmt->execute();
    $stmt->bind_result($description, $submission_datetime, $recorded_by_name);

    if ($stmt->fetch()) {
        $stmt->close();
        return [
            'description' => $description,
            'submission_datetime' => $submission_datetime,
            'recorded_by_name' => $recorded_by_name ?: 'Unknown',
        ];
    }

    $stmt->close();
    return null;
}

function getExcludedMemberIds($conn, $head_parish_id, $harambee_target, $harambee_id) {
    $sql = "SELECT member_id 
            FROM harambee_exclusions
            WHERE head_parish_id = ? AND harambee_target = ? AND harambee_id = ?";

    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        return [];
    }

    $stmt->bind_param("isi", $head_parish_id, $harambee_target, $harambee_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $excludedMemberIds = [];
    while ($row = $result->fetch_assoc()) {
        $excludedMemberIds[] = $row['member_id'];
    }

    $stmt->close();
    return $excludedMemberIds;
}

function getGroupedExpenseDescription($conn, $groupedExpenseId, $target) {
    $tables = [
        'head-parish' => 'head_parish_grouped_expense_requests',
        'sub-parish'  => 'sub_parish_grouped_expense_requests',
        'community'   => 'community_grouped_expense_requests',
        'group'       => 'group_grouped_expense_requests',
    ];

    if (!isset($tables[$target])) {
        return null; // Invalid target
    }

    $table = $tables[$target];

    $sql = "SELECT description FROM {$table} WHERE grouped_request_id = ?";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Failed to prepare statement: " . $conn->error);
        return null;
    }

    $stmt->bind_param("i", $groupedExpenseId);
    $stmt->execute();
    $stmt->bind_result($description);
    $result = $stmt->fetch();
    $stmt->close();

    return $result ? $description : null;
}

function getTransactionHistory(
    mysqli $conn,
    int $account_id,
    string $management_level,
    string $start_date,
    string $end_date,
    int $head_parish_id,
    ?int $sub_parish_id = null,
    ?int $community_id = null,
    ?int $group_id = null
) {
    $result = [
        "success" => false,
        "opening_balance" => 0,
        "transactions" => []
    ];

    // Normalize level
    $management_level = strtolower(trim($management_level));

    // Validate management level
    if (!in_array($management_level, ['head-parish','sub-parish','community','group'])) {
        $result["message"] = "Invalid management level";
        return $result;
    }

    // Opening balance calculation
    $opening_balance = 0;

    if ($management_level === 'head-parish') {
        // Step 1: base opening balance from bank accounts table
        $sql = "SELECT balance FROM head_parish_bank_accounts 
                WHERE account_id = ? AND head_parish_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $account_id, $head_parish_id);
        $stmt->execute();
        $stmt->bind_result($base_balance);
        $stmt->fetch();
        $stmt->close();

        $base_balance = $base_balance ?? 0;

        // Step 2: revenues before start date
        $sql = "SELECT 
                    COALESCE(SUM(CASE WHEN type='revenue' THEN amount ELSE 0 END),0) -
                    COALESCE(SUM(CASE WHEN type='expense' THEN amount ELSE 0 END),0)
                FROM transactions
                WHERE account_id = ? AND head_parish_id = ? AND txn_date < ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("iis", $account_id, $head_parish_id, $start_date);
        $stmt->execute();
        $stmt->bind_result($net_before);
        $stmt->fetch();
        $stmt->close();

        $opening_balance = $base_balance + $net_before;
    } 
    else {
        // For sub-parish / community / group
        $whereClause = "";
        $params = [$account_id, $head_parish_id, $start_date];
        $types = "iis";

        if ($management_level === 'sub-parish') {
            $whereClause = "AND sub_parish_id = ? AND community_id IS NULL";
            $params[] = $sub_parish_id;
            $types .= "i";
        } elseif ($management_level === 'community') {
            $whereClause = "AND sub_parish_id = ? AND community_id = ?";
            $params[] = $sub_parish_id;
            $params[] = $community_id;
            $types .= "ii";
        } elseif ($management_level === 'group') {
            $whereClause = "AND group_id = ?";
            $params[] = $group_id;
            $types .= "i";
        }

        $sql = "SELECT 
                    COALESCE(SUM(CASE WHEN type='revenue' THEN amount ELSE 0 END),0) -
                    COALESCE(SUM(CASE WHEN type='expense' THEN amount ELSE 0 END),0)
                FROM transactions
                WHERE account_id = ? AND head_parish_id = ? AND txn_date < ? $whereClause";

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        $stmt->execute();
        $stmt->bind_result($net_before);
        $stmt->fetch();
        $stmt->close();

        $opening_balance = $net_before ?? 0;
    }

    // Fetch transactions in range
    $whereClause = "";
    $params = [$account_id, $head_parish_id, $start_date, $end_date];
    $types = "iiss";

    if ($management_level === 'sub-parish') {
        $whereClause = "AND sub_parish_id = ? AND community_id IS NULL";
        $params[] = $sub_parish_id;
        $types .= "i";
    } elseif ($management_level === 'community') {
        $whereClause = "AND sub_parish_id = ? AND community_id = ?";
        $params[] = $sub_parish_id;
        $params[] = $community_id;
        $types .= "ii";
    } elseif ($management_level === 'group') {
        $whereClause = "AND group_id = ?";
        $params[] = $group_id;
        $types .= "i";
    }

    $sql = "SELECT transaction_id, type, description, amount, txn_date
            FROM transactions
            WHERE account_id = ? AND head_parish_id = ? 
              AND txn_date BETWEEN ? AND ? $whereClause
            ORDER BY txn_date ASC";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();

    $transactions = [];
    while ($row = $res->fetch_assoc()) {
        $txn_date_formatted = date('Ymd', strtotime($row['txn_date']));
        $row['reference_number'] = "HP{$head_parish_id}-TX{$row['transaction_id']}-{$txn_date_formatted}";
        $transactions[] = $row;
    }
    $stmt->close();

    $result["success"] = true;
    $result["opening_balance"] = $opening_balance;
    $result["transactions"] = $transactions;

    return $result;
}

// Function 1: Get management level names
function getManagementLevelNames(
    mysqli $conn,
    string $management_level,
    int $head_parish_id,
    ?int $sub_parish_id = null,
    ?int $community_id = null,
    ?int $group_id = null
) {
    $result = [
        "head_parish_name" => null,
        "sub_parish_name" => null,
        "community_name" => null,
        "group_name" => null
    ];

    $management_level = strtolower(trim($management_level));

    // Head Parish
    if (in_array($management_level, ['head-parish','sub-parish','community','group'])) {
        $sql = "SELECT head_parish_name FROM head_parishes WHERE head_parish_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $head_parish_id);
        $stmt->execute();
        $stmt->bind_result($head_parish_name);
        $stmt->fetch();
        $stmt->close();
        $result['head_parish_name'] = strtoupper($head_parish_name);
    }

    // Sub Parish
    if (in_array($management_level, ['sub-parish','community','group']) && $sub_parish_id) {
        $sql = "SELECT sub_parish_name FROM sub_parishes WHERE sub_parish_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $sub_parish_id);
        $stmt->execute();
        $stmt->bind_result($sub_parish_name);
        $stmt->fetch();
        $stmt->close();
        $result['sub_parish_name'] = strtoupper($sub_parish_name);
    }

    // Community
    if (in_array($management_level, ['community','group']) && $community_id) {
        $sql = "SELECT community_name FROM communities WHERE community_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $community_id);
        $stmt->execute();
        $stmt->bind_result($community_name);
        $stmt->fetch();
        $stmt->close();
        $result['community_name'] = strtoupper($community_name);
    }

    // Group
    if ($management_level === 'group' && $group_id) {
        $sql = "SELECT group_name FROM groups WHERE group_id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $group_id);
        $stmt->execute();
        $stmt->bind_result($group_name);
        $stmt->fetch();
        $stmt->close();
        $result['group_name'] = strtoupper($group_name);
    }

    return $result;
}

// Function 2: Get Head Parish Bank Account details
function getHeadParishBankAccount(
    mysqli $conn,
    int $account_id
) {
    $result = [
        "account_name" => null,
        "account_number" => null
    ];

    $sql = "SELECT account_name, account_number FROM head_parish_bank_accounts WHERE account_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $account_id);
    $stmt->execute();
    $stmt->bind_result($account_name, $account_number);
    $stmt->fetch();
    $stmt->close();

    $result['account_name'] = strtoupper($account_name);
    $result['account_number'] = $account_number;

    return $result;
}

function getTotalRevenueAndExpenses(
    mysqli $conn,
    string $management_level,
    int $head_parish_id,
    ?int $sub_parish_id = null,
    ?int $community_id = null,
    ?int $group_id = null
) {
    $management_level = strtolower(trim($management_level));

    $result = [
        "success" => false,
        "total_revenue" => 0,
        "total_expense" => 0
    ];

    try {
        $totalRevenue = 0;
        $totalExpense = 0;

        if ($management_level === 'head-parish') {
            // Total revenues: head_parish_revenues + other_head_parish_revenues
            $sql = "SELECT COALESCE(SUM(revenue_amount),0) FROM head_parish_revenues WHERE head_parish_id = $head_parish_id";
            $res = $conn->query($sql);
            $totalRevenue += $res->fetch_row()[0] ?? 0;

            $sql = "SELECT COALESCE(SUM(revenue_amount),0) FROM other_head_parish_revenues WHERE head_parish_id = $head_parish_id";
            $res = $conn->query($sql);
            $totalRevenue += $res->fetch_row()[0] ?? 0;

            // Total expenses: head_parish_expense_requests (only approved)
            $sql = "SELECT COALESCE(SUM(request_amount),0) FROM head_parish_expense_requests WHERE head_parish_id = $head_parish_id AND request_status='Approved'";
            $res = $conn->query($sql);
            $totalExpense = $res->fetch_row()[0] ?? 0;

        } elseif ($management_level === 'sub-parish') {
            // Total revenues: sub_parish_revenues
            $sql = "SELECT COALESCE(SUM(revenue_amount),0) FROM sub_parish_revenues WHERE head_parish_id = $head_parish_id AND sub_parish_id = $sub_parish_id";
            $res = $conn->query($sql);
            $totalRevenue = $res->fetch_row()[0] ?? 0;

            // Total expenses: sub_parish_expense_requests (only approved)
            $sql = "SELECT COALESCE(SUM(request_amount),0) FROM sub_parish_expense_requests WHERE head_parish_id = $head_parish_id AND sub_parish_id = $sub_parish_id AND request_status='Approved'";
            $res = $conn->query($sql);
            $totalExpense = $res->fetch_row()[0] ?? 0;

        } elseif ($management_level === 'community') {
            // Total revenues: community_revenues
            $sql = "SELECT COALESCE(SUM(revenue_amount),0) FROM community_revenues WHERE head_parish_id = $head_parish_id AND sub_parish_id = $sub_parish_id AND community_id = $community_id";
            $res = $conn->query($sql);
            $totalRevenue = $res->fetch_row()[0] ?? 0;

            // Total expenses: community_expense_requests (only approved)
            $sql = "SELECT COALESCE(SUM(request_amount),0) FROM community_expense_requests WHERE head_parish_id = $head_parish_id AND sub_parish_id = $sub_parish_id AND community_id = $community_id AND request_status='Approved'";
            $res = $conn->query($sql);
            $totalExpense = $res->fetch_row()[0] ?? 0;

        } elseif ($management_level === 'group') {
            // Total revenues: group_revenues
            $sql = "SELECT COALESCE(SUM(revenue_amount),0) FROM group_revenues WHERE head_parish_id = $head_parish_id AND group_id = $group_id";
            $res = $conn->query($sql);
            $totalRevenue = $res->fetch_row()[0] ?? 0;

            // Total expenses: group_expense_requests (only approved)
            $sql = "SELECT COALESCE(SUM(request_amount),0) FROM group_expense_requests WHERE head_parish_id = $head_parish_id AND group_id = $group_id AND request_status='Approved'";
            $res = $conn->query($sql);
            $totalExpense = $res->fetch_row()[0] ?? 0;

        } else {
            $result["message"] = "Invalid management level";
            return $result;
        }

        $result["success"] = true;
        $result["total_revenue"] = $totalRevenue;
        $result["total_expense"] = $totalExpense;

    } catch (Exception $e) {
        $result["message"] = $e->getMessage();
    }

    return $result;
}

function getSystemAdmins(
    mysqli $conn,
    string $management_level,
    int $head_parish_id,
    ?int $sub_parish_id = null,
    ?int $community_id = null,
    ?int $group_id = null
) {
    $management_level = strtolower(trim($management_level));
    $admins = [];
    $allowed_roles = "'accountant','chairperson','secretary', 'elder'";

    try {
        if ($management_level === 'head-parish') {
            $sql = "SELECT 
                        head_parish_admin_fullname AS fullname, 
                        head_parish_admin_phone AS phone, 
                        head_parish_admin_role AS role
                    FROM head_parish_admins 
                    WHERE head_parish_id = $head_parish_id
                      AND head_parish_admin_role IN ($allowed_roles)";
        } elseif ($management_level === 'sub-parish') {
            $sql = "SELECT 
                        sub_parish_admin_fullname AS fullname, 
                        sub_parish_admin_phone AS phone, 
                        sub_parish_admin_role AS role
                    FROM sub_parish_admins 
                    WHERE head_parish_id = $head_parish_id 
                      AND sub_parish_id = $sub_parish_id
                      AND sub_parish_admin_role IN ($allowed_roles)";
        } elseif ($management_level === 'community') {
            $sql = "SELECT 
                        community_admin_fullname AS fullname, 
                        community_admin_phone AS phone, 
                        community_admin_role AS role
                    FROM community_admins 
                    WHERE head_parish_id = $head_parish_id 
                      AND sub_parish_id = $sub_parish_id 
                      AND community_id = $community_id
                      AND community_admin_role IN ($allowed_roles)";
        } elseif ($management_level === 'group') {
            $sql = "SELECT 
                        group_admin_fullname AS fullname, 
                        group_admin_phone AS phone, 
                        group_admin_role AS role
                    FROM group_admins 
                    WHERE head_parish_id = $head_parish_id 
                      AND group_id = $group_id
                      AND group_admin_role IN ($allowed_roles)";
        } else {
            return ["success" => false, "message" => "Invalid management level"];
        }

        $res = $conn->query($sql);
        if ($res && $res->num_rows > 0) {
            while ($row = $res->fetch_assoc()) {
                // First name only
                $first_name = ucfirst(strtolower(explode(' ', trim($row['fullname']))[0]));

                $admins[] = [
                    "name" => $first_name,
                    "phone" => $row['phone'],
                    "role" => strtolower(trim($row['role']))
                ];
            }
        }

        return ["success" => true, "admins" => $admins];

    } catch (Exception $e) {
        return ["success" => false, "message" => $e->getMessage()];
    }
}

function notifyAdminsSMS(
    mysqli $conn,
    string $management_level,
    string $management_level_name,
    string $transaction_type,
    string $transaction_name,
    float $amount,
    float $total_revenue,
    float $total_expense,
    string $admin_name,
    string $admin_phone,
    int $head_parish_id,
    string $transaction_date
) {
    date_default_timezone_set('Africa/Dar_es_Salaam');

    $smsInfo = get_sms_credentials($conn, $head_parish_id);
    if (!$smsInfo) {
        error_log("No SMS credentials found for head parish ID $head_parish_id");
        return false;
    }

    $apiToken = $smsInfo['api_token'];
    $senderId = $smsInfo['sender_id'];

    // Prepare management level display
    $level_display_name = '';
    switch (strtolower($management_level)) {
        case 'head-parish':
            $level_display_name = '';
            break;
        case 'sub-parish':
            $level_display_name = 'MTAA WA ' . strtoupper($management_level_name);
            break;
        case 'community':
            $level_display_name = 'JUMUIYA YA ' . strtoupper($management_level_name);
            break;
        case 'group':
        case 'groups':
            $level_display_name = strtoupper($management_level_name);
            break;
        default:
            $level_display_name = strtoupper($management_level_name);
    }

    // ---- DATE LOGIC + DISPLAY TEXT ----
    $txDateYmd = date('Y-m-d', strtotime($transaction_date)); // normalize
    $todayYmd  = date('Y-m-d');

    $isToday = ($txDateYmd === $todayYmd);
    $isPast  = ($txDateYmd < $todayYmd);

    $txDateDisplay = date('d/m/Y', strtotime($txDateYmd)); // dd/mm/YYYY
    $datePrefix = $isToday ? "Leo tarehe $txDateDisplay, " : "Tarehe $txDateDisplay, ";
    // -----------------------------------

    // Choose verbs based on past/today (future will use "imepokea/kimetolewa" as today)
    $receivedVerb = $isPast ? 'ilipokea' : 'imepokea';
    $paidVerb     = $isPast ? 'kilitolewa' : 'kimetolewa';

    // Construct SMS message
    if (strtolower($transaction_type) === 'revenue') {
        $message = ($level_display_name ? $level_display_name . "\n" : "") .
                   "Shalom " . ucfirst(strtolower($admin_name)) . ".\n" .
                   $datePrefix .
                   "akaunti yenu $receivedVerb mapato ya Shs. " . number_format($amount, 0) .
                   " yatokanayo na $transaction_name.\n";
    } else { // expense
        $message = ($level_display_name ? $level_display_name . "\n" : "") .
                   "Shalom " . ucfirst(strtolower($admin_name)) . ".\n" .
                   $datePrefix .
                   "kiasi cha Shs. " . number_format($amount, 0) .
                   " $paidVerb kwenye akaunti yenu kwa ajili ya $transaction_name.\n";
    }

    // Append totals and balance
    $balance = $total_revenue - $total_expense;
    // $message .= "Jumla ya Mapato: Shs. " . number_format($total_revenue, 0) . "\n";
    // $message .= "Jumla ya Matumizi: Shs. " . number_format($total_expense, 0) . "\n";
    $message .= "Salio: Shs. " . number_format($balance, 0);

    // Send SMS
    $smsClient = new SewmrSMSClient($apiToken, $senderId);
    $admin_phone = preg_replace('/^0/', '255', $admin_phone);
    $response = $smsClient->sendQuickSMS(null, $message, [$admin_phone]);

    if (!isset($response['success']) || $response['success'] !== true) {
        $errorMessage = $response['message'] ?? "Unknown error";
        error_log("Failed to send SMS to {$admin_phone}: $errorMessage");
        return false;
    }

    return true;
}


function getExpenseRequestDetails($conn, $target, $request_id) {
    $tables = [
        'head-parish' => [
            'request_table' => 'head_parish_expense_requests',
            'group_table'   => 'head_parish_expense_groups',
            'name_table'    => 'head_parish_expense_names',
            'grouped_table' => 'head_parish_grouped_expense_requests',
            'sub_parish_id' => null,
            'community_id'  => null,
            'group_id'      => null
        ],
        'sub-parish' => [
            'request_table' => 'sub_parish_expense_requests',
            'group_table'   => 'sub_parish_expense_groups',
            'name_table'    => 'sub_parish_expense_names',
            'grouped_table' => 'sub_parish_grouped_expense_requests',
            'sub_parish_id' => 'sub_parish_id',
            'community_id'  => null,
            'group_id'      => null
        ],
        'community' => [
            'request_table' => 'community_expense_requests',
            'group_table'   => 'community_expense_groups',
            'name_table'    => 'community_expense_names',
            'grouped_table' => 'community_grouped_expense_requests',
            'sub_parish_id' => 'sub_parish_id',
            'community_id'  => 'community_id',
            'group_id'      => null
        ],
        'group' => [
            'request_table' => 'group_expense_requests',
            'group_table'   => 'group_expense_groups',
            'name_table'    => 'group_expense_names',
            'grouped_table' => 'group_grouped_expense_requests',
            'sub_parish_id' => null,
            'community_id'  => null,
            'group_id'      => 'group_id'
        ]
    ];

    if (!isset($tables[$target])) return null;

    $info = $tables[$target];
    $r = $info['request_table'];   // expense_requests
    $g = $info['group_table'];     // expense_groups
    $n = $info['name_table'];      // expense_names
    $gr = $info['grouped_table'];  // grouped_expense_requests

    $sql = "SELECT r.request_amount, 
                   n.expense_name,
                   g.account_id,
                   gr.recorded_by,
                   DATE(r.request_datetime) AS expense_date,
                   ".($info['sub_parish_id'] ? "r.sub_parish_id" : "NULL")." AS sub_parish_id,
                   ".($info['community_id'] ? "r.community_id" : "NULL")." AS community_id,
                   ".($info['group_id'] ? "r.group_id" : "NULL")." AS group_id
            FROM $r r
            JOIN $n n ON r.expense_name_id = n.expense_name_id
            JOIN $g g ON r.expense_group_id = g.expense_group_id
            LEFT JOIN $gr gr ON r.grouped_request_id = gr.grouped_request_id
            WHERE r.request_id = ?";

    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $request_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $data = $result->fetch_assoc();
    $stmt->close();

    return $data ?: null;
}

function getExpenseGroupSummary($conn, $target, $head_parish_id, $sub_parish_id, $community_id, $group_id, $expense_group_id, $start_date, $end_date) {
    $tables = [
        'head-parish' => [
            'request_table' => 'head_parish_expense_requests',
            'group_table'   => 'head_parish_expense_groups',
            'name_table'    => 'head_parish_expense_names',
            'budget_table'  => 'head_parish_expense_budgets',
            'filters'       => ['head_parish_id' => 'head_parish_id'],
        ],
        'sub-parish' => [
            'request_table' => 'sub_parish_expense_requests',
            'group_table'   => 'sub_parish_expense_groups',
            'name_table'    => 'sub_parish_expense_names',
            'budget_table'  => 'sub_parish_expense_budgets',
            'filters'       => ['head_parish_id' => 'head_parish_id', 'sub_parish_id' => 'sub_parish_id'],
        ],
        'community' => [
            'request_table' => 'community_expense_requests',
            'group_table'   => 'community_expense_groups',
            'name_table'    => 'community_expense_names',
            'budget_table'  => 'community_expense_budgets',
            'filters'       => ['head_parish_id' => 'head_parish_id', 'sub_parish_id' => 'sub_parish_id', 'community_id' => 'community_id'],
        ],
        'group' => [
            'request_table' => 'group_expense_requests',
            'group_table'   => 'group_expense_groups',
            'name_table'    => 'group_expense_names',
            'budget_table'  => 'group_expense_budgets',
            'filters'       => ['head_parish_id' => 'head_parish_id', 'group_id' => 'group_id'],
        ]
    ];

    if (!isset($tables[$target])) return null;

    $info = $tables[$target];
    $r = $info['request_table'];
    $g = $info['group_table'];
    $n = $info['name_table'];
    $b = $info['budget_table'];

    // build management filters (for queries against requests/budgets)
    $mgmtFilterClauses = [];
    $mgmtParams = [];
    $mgmtTypes = '';
    foreach ($info['filters'] as $key => $col) {
        $mgmtFilterClauses[] = "r.$col = ?";
        switch ($col) {
            case 'head_parish_id': $mgmtParams[] = $head_parish_id; $mgmtTypes .= 'i'; break;
            case 'sub_parish_id':  $mgmtParams[] = $sub_parish_id;  $mgmtTypes .= 'i'; break;
            case 'community_id':   $mgmtParams[] = $community_id;   $mgmtTypes .= 'i'; break;
            case 'group_id':       $mgmtParams[] = $group_id;       $mgmtTypes .= 'i'; break;
        }
    }
    $mgmtClause = '';
    if (!empty($mgmtFilterClauses)) {
        $mgmtClause = ' AND ' . implode(' AND ', $mgmtFilterClauses);
    }

    // --- NEW: compute year start and determine whether we have a previous period ---
    // previous is strictly before start_date AND within the same calendar year as start_date
    $totalPreviousExpenses = 0.0;
    $prevPerNameMap = [];

    // Normalize start_date to YYYY-MM-DD (ensure no time component)
    $start_date = date('Y-m-d', strtotime($start_date));
    $end_date = date('Y-m-d', strtotime($end_date));

    // If start_date is Jan 1, we intentionally have no "previous" period (previous = 0).
    if (date('m-d', strtotime($start_date)) !== '01-01') {
        // year start for the start_date's year (e.g., 2025-01-01)
        $year_start = date('Y-01-01', strtotime($start_date));

        // 1) Compute total previous expenses for the entire expense group within same year and strictly before start_date
        $prevGroupSQL = "SELECT SUM(r.request_amount) as total_previous
                         FROM $r r
                         WHERE r.expense_group_id = ?
                           AND r.request_status = 'Approved'
                           AND DATE(r.request_datetime) >= ?
                           AND DATE(r.request_datetime) < ?" . $mgmtClause;

        $prevGroupParams = array_merge([$expense_group_id, $year_start, $start_date], $mgmtParams);
        $prevGroupTypes  = 'iss' . $mgmtTypes;

        $stmtPrevGroup = $conn->prepare($prevGroupSQL);
        if ($stmtPrevGroup) {
            $stmtPrevGroup->bind_param($prevGroupTypes, ...$prevGroupParams);
            $stmtPrevGroup->execute();
            $pgRes = $stmtPrevGroup->get_result()->fetch_assoc();
            $stmtPrevGroup->close();
            $totalPreviousExpenses = $pgRes && $pgRes['total_previous'] ? (float)$pgRes['total_previous'] : 0.0;
        }
    }

    // 2) Query current range expenses grouped by expense_name (this preserves the existing expenses array behaviour)
    //    We show expenses array only for names that had approved requests in the selected range.
    $currWhere = "n.expense_group_id = ? AND r.request_status = 'Approved' AND DATE(r.request_datetime) BETWEEN ? AND ? " . $mgmtClause;
    $currSQL = "
        SELECT n.expense_name, n.expense_name_id, SUM(r.request_amount) as total_expense
        FROM $r r
        JOIN $n n ON r.expense_name_id = n.expense_name_id
        WHERE $currWhere
        GROUP BY n.expense_name_id, n.expense_name
    ";

    $currParams = array_merge([$expense_group_id, $start_date, $end_date], $mgmtParams);
    $currTypes  = 'iss' . $mgmtTypes;

    $stmtCurr = $conn->prepare($currSQL);
    if (!$stmtCurr) { return null; }
    $stmtCurr->bind_param($currTypes, ...$currParams);
    $stmtCurr->execute();
    $res = $stmtCurr->get_result();

    $expenses = [];
    $totalCurrentExpenses = 0.0;

    // collect expense ids we need previous totals for (only those shown in the expenses array)
    $expenseIds = [];
    $currentRows = []; // store rows to process budgets and previous-per-name in bulk

    while ($row = $res->fetch_assoc()) {
        $expense_name_id = (int)$row['expense_name_id'];
        $current_total = (float)$row['total_expense'];
        $totalCurrentExpenses += $current_total;

        $expenseIds[] = $expense_name_id;
        $currentRows[$expense_name_id] = [
            'expense_name' => $row['expense_name'],
            'expense_name_id' => $expense_name_id,
            'total_current' => $current_total
        ];
    }
    $stmtCurr->close();

    // 3) Get previous totals per expense_name for those expenseIds (one query)
    if (!empty($expenseIds) && date('m-d', strtotime($start_date)) !== '01-01') {
        // create placeholders for IN(...)
        $placeholders = implode(',', array_fill(0, count($expenseIds), '?'));

        // previous totals restricted to same-year and strictly before start_date
        $year_start = date('Y-01-01', strtotime($start_date));
        $prevPerNameSQL = "SELECT r.expense_name_id, SUM(r.request_amount) as previous_total
                           FROM $r r
                           WHERE r.expense_group_id = ?
                             AND r.request_status = 'Approved'
                             AND DATE(r.request_datetime) >= ?
                             AND DATE(r.request_datetime) < ?
                             AND r.expense_name_id IN ($placeholders) " . $mgmtClause . "
                           GROUP BY r.expense_name_id";

        // build params/types: expense_group_id (i), year_start (s), start_date (s), then each expense id (i), then mgmt params
        $prevPerNameParams = array_merge([$expense_group_id, $year_start, $start_date], $expenseIds, $mgmtParams);
        $prevPerNameTypes  = 'iss' . str_repeat('i', count($expenseIds)) . $mgmtTypes;

        $stmtPrevPerName = $conn->prepare($prevPerNameSQL);
        if ($stmtPrevPerName) {
            $stmtPrevPerName->bind_param($prevPerNameTypes, ...$prevPerNameParams);
            $stmtPrevPerName->execute();
            $pRes = $stmtPrevPerName->get_result();
            while ($prow = $pRes->fetch_assoc()) {
                $prevPerNameMap[(int)$prow['expense_name_id']] = $prow['previous_total'] ? (float)$prow['previous_total'] : 0.0;
            }
            $stmtPrevPerName->close();
        }
    }
    // if expenseIds empty or start_date is Jan 1, prevPerNameMap remains empty -> previous totals 0 for each name

    // 4) For each expense in currentRows, fetch budget (existing logic) and assemble the expense rows
    foreach ($currentRows as $id => $infoRow) {
        $expense_name_id = $id;
        $current_total = $infoRow['total_current'];
        $previous_total = isset($prevPerNameMap[$id]) ? $prevPerNameMap[$id] : 0.0;

        // Find budget that covers/overlaps the selected range (original logic preserved)
        $sqlBudget = "
            SELECT budgeted_amount, start_date as budget_start, end_date as budget_end
            FROM $b
            WHERE expense_group_id = ?
              AND expense_name_id = ?
              AND start_date <= ?
              AND end_date >= ?
            LIMIT 1
        ";
        $stmtBudget = $conn->prepare($sqlBudget);
        if ($stmtBudget) {
            // bind: expense_group_id (i), expense_name_id (i), end_date (s), start_date (s)
            $stmtBudget->bind_param('iiss', $expense_group_id, $expense_name_id, $end_date, $start_date);
            $stmtBudget->execute();
            $budgetRes = $stmtBudget->get_result()->fetch_assoc();
            $stmtBudget->close();
        } else {
            $budgetRes = null;
        }

        $budgeted_amount = $budgetRes ? (float)$budgetRes['budgeted_amount'] : 0.0;

        $balance_for_name = $budgeted_amount - ($previous_total + $current_total);

        $expenses[] = [
            'expense_name' => $infoRow['expense_name'],
            'expense_name_id' => $expense_name_id,
            'budgeted_amount' => $budgeted_amount,
            'total_expenses_before_date' => $previous_total,
            'total_expenses' => $current_total,
            'balance' => $balance_for_name
        ];
    }

    // 5) Get group name (same as before; apply group-level filters)
    $groupFilters = [];
    $groupParams = [$expense_group_id];
    $groupTypes  = 'i';
    $groupFilters[] = "expense_group_id = ?";
    foreach ($info['filters'] as $key => $col) {
        $groupFilters[] = "$col = ?";
        switch ($col) {
            case 'head_parish_id': $groupParams[] = $head_parish_id; $groupTypes .= 'i'; break;
            case 'sub_parish_id':  $groupParams[] = $sub_parish_id;  $groupTypes .= 'i'; break;
            case 'community_id':   $groupParams[] = $community_id;   $groupTypes .= 'i'; break;
            case 'group_id':       $groupParams[] = $group_id;       $groupTypes .= 'i'; break;
        }
    }
    $stmt = $conn->prepare("SELECT expense_group_name FROM $g WHERE ".implode(" AND ", $groupFilters)." LIMIT 1");
    if ($stmt) {
        $stmt->bind_param($groupTypes, ...$groupParams);
        $stmt->execute();
        $groupRes = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    } else {
        $groupRes = null;
    }

    $groupName = $groupRes ? strtoupper($groupRes['expense_group_name']) : 'UNKNOWN';

    // 6) Calculate group budget: sum of budgets overlapping the selected range (same as before)
    $budgetWhere = ["expense_group_id = ?", "start_date <= ?", "end_date >= ?"];
    $budgetParams = [$expense_group_id, $end_date, $start_date];
    $budgetTypes = 'iss';
    foreach ($info['filters'] as $key => $col) {
        $budgetWhere[] = "$col = ?";
        switch ($col) {
            case 'head_parish_id': $budgetParams[] = $head_parish_id; $budgetTypes .= 'i'; break;
            case 'sub_parish_id':  $budgetParams[] = $sub_parish_id;  $budgetTypes .= 'i'; break;
            case 'community_id':   $budgetParams[] = $community_id;   $budgetTypes .= 'i'; break;
            case 'group_id':       $budgetParams[] = $group_id;       $budgetTypes .= 'i'; break;
        }
    }

    $stmt = $conn->prepare("SELECT SUM(budgeted_amount) as total_budget FROM $b WHERE ".implode(" AND ", $budgetWhere));
    if ($stmt) {
        $stmt->bind_param($budgetTypes, ...$budgetParams);
        $stmt->execute();
        $budgetRes = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    } else {
        $budgetRes = null;
    }

    $annualBudget = $budgetRes && $budgetRes['total_budget'] ? (float)$budgetRes['total_budget'] : 0.0;

    // Group level totals and balance
    $balance = $annualBudget - ($totalPreviousExpenses + $totalCurrentExpenses);

    return [
        'expense_group_name' => $groupName,
        'annual_budget' => $annualBudget,
        'total_expenses_before_date' => $totalPreviousExpenses,
        'total_expenses' => $totalCurrentExpenses,
        'balance' => $balance,
        'expenses' => $expenses
    ];
}



function getExpenseNameSummary(
    $conn,
    $target,
    $head_parish_id,
    $sub_parish_id,
    $community_id,
    $group_id,
    $expense_group_id,
    $expense_name_id, // mandatory
    $start_date,
    $end_date
) {
    if (!$expense_name_id) return null;

    $tables = [
        'head-parish' => [
            'request_table' => 'head_parish_expense_requests',
            'group_table'   => 'head_parish_expense_groups',
            'name_table'    => 'head_parish_expense_names',
            'budget_table'  => 'head_parish_expense_budgets',
            'filters'       => ['head_parish_id' => 'head_parish_id'],
        ],
        'sub-parish' => [
            'request_table' => 'sub_parish_expense_requests',
            'group_table'   => 'sub_parish_expense_groups',
            'name_table'    => 'sub_parish_expense_names',
            'budget_table'  => 'sub_parish_expense_budgets',
            'filters'       => ['head_parish_id' => 'head_parish_id', 'sub_parish_id' => 'sub_parish_id'],
        ],
        'community' => [
            'request_table' => 'community_expense_requests',
            'group_table'   => 'community_expense_groups',
            'name_table'    => 'community_expense_names',
            'budget_table'  => 'community_expense_budgets',
            'filters'       => ['head_parish_id' => 'head_parish_id', 'sub_parish_id' => 'sub_parish_id', 'community_id' => 'community_id'],
        ],
        'group' => [
            'request_table' => 'group_expense_requests',
            'group_table'   => 'group_expense_groups',
            'name_table'    => 'group_expense_names',
            'budget_table'  => 'group_expense_budgets',
            'filters'       => ['head_parish_id' => 'head_parish_id', 'group_id' => 'group_id'],
        ]
    ];

    if (!isset($tables[$target])) return null;

    $info = $tables[$target];
    $r = $info['request_table'];
    $g = $info['group_table'];
    $n = $info['name_table'];
    $b = $info['budget_table'];

    // 1) fetch expense name (display)
    $expenseName = null;
    $stmt = $conn->prepare("SELECT expense_name FROM $n WHERE expense_name_id = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param('i', $expense_name_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $expenseName = $res ? $res['expense_name'] : null;
        $stmt->close();
    }

    // helper to build WHERE + params + types
    $buildFilters = function($extraClauses = [], $extraParams = [], $extraTypes = '') use ($info, $head_parish_id, $sub_parish_id, $community_id, $group_id) {
        $filters = [];
        $params = [];
        $types = '';

        foreach ($info['filters'] as $col) {
            $filters[] = "r.$col = ?";
            switch ($col) {
                case 'head_parish_id': $params[] = $head_parish_id; $types .= 'i'; break;
                case 'sub_parish_id':  $params[] = $sub_parish_id;  $types .= 'i'; break;
                case 'community_id':   $params[] = $community_id;   $types .= 'i'; break;
                case 'group_id':       $params[] = $group_id;       $types .= 'i'; break;
            }
        }

        // append extra clauses (e.g. expense_group_id, expense_name_id, date range)
        foreach ($extraClauses as $cl) {
            $filters[] = $cl;
        }
        $params = array_merge($params, $extraParams);
        $types  .= $extraTypes;

        return [implode(" AND ", $filters), $params, $types];
    };

    // Normalize dates to Y-m-d to avoid time-component issues
    $start_date = date('Y-m-d', strtotime($start_date));
    $end_date   = date('Y-m-d', strtotime($end_date));

    // 2) find applicable budget for this expense name that covers the selected range (if any)
    $budgeted_amount = 0.0;
    $budget_start_date = $start_date;
    $stmtBudget = $conn->prepare("
        SELECT budgeted_amount, start_date as budget_start, end_date as budget_end
        FROM $b
        WHERE expense_group_id = ?
          AND expense_name_id = ?
          AND start_date <= ?
          AND end_date >= ?
        LIMIT 1
    ");
    if ($stmtBudget) {
        $stmtBudget->bind_param('iiss', $expense_group_id, $expense_name_id, $end_date, $start_date);
        $stmtBudget->execute();
        $budgetRes = $stmtBudget->get_result()->fetch_assoc();
        if ($budgetRes) {
            $budgeted_amount = (float)$budgetRes['budgeted_amount'];
            $budget_start_date = date('Y-m-d', strtotime($budgetRes['budget_start']));
        }
        $stmtBudget->close();
    }

    // --- NEW: compute previous_total using same-year & strictly-before-start logic ---
    $previous_total = 0.0;

    // If start_date is NOT Jan 1, we compute previous totals; otherwise previous = 0
    if (date('m-d', strtotime($start_date)) !== '01-01') {
        // year start for the start_date's year (e.g., 2025-01-01)
        $year_start = date('Y-01-01', strtotime($start_date));

        // previous period should not go before the start of the year OR before the budget start;
        // take the later of those two
        $previous_start_candidate = date('Y-m-d', strtotime($budget_start_date));
        $previous_start = (strtotime($previous_start_candidate) > strtotime($year_start)) ? $previous_start_candidate : $year_start;

        // previous_end is strictly before start_date
        $previous_end = date('Y-m-d', strtotime($start_date . ' -1 day'));

        if (strtotime($previous_end) >= strtotime($previous_start)) {
            // Build filters: management filters + expense_group_id + expense_name_id + approved + date range
            // Use >= previous_start AND < start_date semantics to avoid double-count with start_date
            $prevExtraClauses = [
                "r.expense_group_id = ?",
                "r.expense_name_id = ?",
                "r.request_status = 'Approved'",
                "DATE(r.request_datetime) >= ?",
                "DATE(r.request_datetime) < ?"
            ];
            $prevExtraParams  = [$expense_group_id, $expense_name_id, $previous_start, $start_date];
            $prevExtraTypes   = 'iiss';

            list($prevWhereSQL, $prevParams, $prevTypes) = $buildFilters($prevExtraClauses, $prevExtraParams, $prevExtraTypes);

            $prevSQL = "SELECT SUM(r.request_amount) as previous_total FROM $r r WHERE $prevWhereSQL";
            $stmtPrev = $conn->prepare($prevSQL);
            if ($stmtPrev) {
                $stmtPrev->bind_param($prevTypes, ...$prevParams);
                $stmtPrev->execute();
                $pRes = $stmtPrev->get_result()->fetch_assoc();
                $previous_total = $pRes && $pRes['previous_total'] ? (float)$pRes['previous_total'] : 0.0;
                $stmtPrev->close();
            }
        } // else previous range empty -> previous_total stays 0
    }
    // --- END previous_total logic ---

    // 4) fetch individual approved expense requests for this expense_name in selected range (date + amount)
    $expenses = [];
    $current_total = 0.0;
    $extraClauses = ["r.expense_group_id = ?", "r.expense_name_id = ?", "r.request_status = 'Approved'", "DATE(r.request_datetime) BETWEEN ? AND ?"];
    $extraParams  = [$expense_group_id, $expense_name_id, $start_date, $end_date];
    $extraTypes   = 'iiss';

    list($whereSQL, $params, $types) = $buildFilters($extraClauses, $extraParams, $extraTypes);

    $sqlRows = "SELECT r.request_id, r.request_amount, DATE(r.request_datetime) as date, r.request_description FROM $r r WHERE $whereSQL ORDER BY r.request_datetime ASC";
    $stmtRows = $conn->prepare($sqlRows);
    if ($stmtRows) {
        // bind params (types may include management filters + extraTypes)
        if (!empty($types)) {
            $stmtRows->bind_param($types, ...$params);
        }
        $stmtRows->execute();
        $resRows = $stmtRows->get_result();
        while ($row = $resRows->fetch_assoc()) {
            $amt = $row['request_amount'] !== null ? (float)$row['request_amount'] : 0.0;
            $expenses[] = [
                'date' => $row['date'],
                'amount' => $amt,
                'request_id' => isset($row['request_id']) ? (int)$row['request_id'] : null,
                'description' => isset($row['request_description']) ? $row['request_description'] : ''
            ];
            $current_total += $amt;
        }
        $stmtRows->close();
    }

    // 5) compute annualBudget for this expense_name in the selected range (sum of budgets overlapping the selected range)
    $budgetWhere = ["expense_group_id = ?", "expense_name_id = ?", "start_date <= ?", "end_date >= ?"];
    $budgetParams = [$expense_group_id, $expense_name_id, $end_date, $start_date];
    $budgetTypes = 'iiss';
    foreach ($info['filters'] as $col) {
        $budgetWhere[] = "$col = ?";
        switch ($col) {
            case 'head_parish_id': $budgetParams[] = $head_parish_id; $budgetTypes .= 'i'; break;
            case 'sub_parish_id':  $budgetParams[] = $sub_parish_id;  $budgetTypes .= 'i'; break;
            case 'community_id':   $budgetParams[] = $community_id;   $budgetTypes .= 'i'; break;
            case 'group_id':       $budgetParams[] = $group_id;       $budgetTypes .= 'i'; break;
        }
    }
    $stmt = $conn->prepare("SELECT SUM(budgeted_amount) as total_budget FROM $b WHERE " . implode(" AND ", $budgetWhere));
    $annualBudget = 0.0;
    if ($stmt) {
        $stmt->bind_param($budgetTypes, ...$budgetParams);
        $stmt->execute();
        $bRes = $stmt->get_result()->fetch_assoc();
        $annualBudget = $bRes && $bRes['total_budget'] ? (float)$bRes['total_budget'] : 0.0;
        $stmt->close();
    }

    // 6) group name (apply management filters)
    $groupFilters = [];
    $groupParams = [$expense_group_id];
    $groupTypes  = 'i';
    $groupFilters[] = "expense_group_id = ?";
    foreach ($info['filters'] as $col) {
        $groupFilters[] = "$col = ?";
        switch ($col) {
            case 'head_parish_id': $groupParams[] = $head_parish_id; $groupTypes .= 'i'; break;
            case 'sub_parish_id':  $groupParams[] = $sub_parish_id;  $groupTypes .= 'i'; break;
            case 'community_id':   $groupParams[] = $community_id;   $groupTypes .= 'i'; break;
            case 'group_id':       $groupParams[] = $group_id;       $groupTypes .= 'i'; break;
        }
    }
    $stmt = $conn->prepare("SELECT expense_group_name FROM $g WHERE ".implode(" AND ", $groupFilters)." LIMIT 1");
    if ($stmt) {
        $stmt->bind_param($groupTypes, ...$groupParams);
        $stmt->execute();
        $groupRes = $stmt->get_result()->fetch_assoc();
        $stmt->close();
    } else {
        $groupRes = null;
    }
    $groupName = $groupRes ? strtoupper($groupRes['expense_group_name']) : 'UNKNOWN';

    // 7) balances
    $balance_for_name = $budgeted_amount - ($previous_total + $current_total);
    $balance_group_level = $annualBudget - ($previous_total + $current_total); // here annualBudget is for this expense_name

    return [
        'expense_group_name' => $groupName,
        'annual_budget' => $annualBudget,
        'total_expenses_before_date' => $previous_total,
        'total_expenses' => $current_total,
        'balance' => $balance_group_level,
        'expense_name' => $expenseName,
        'expense_name_id' => (int)$expense_name_id,
        'budgeted_amount' => $budgeted_amount,
        'expenses' => $expenses // array of {date, amount, request_id}
    ];
}

function getRevenueGroupSummary($conn, $target, $head_parish_id, $sub_parish_id, $community_id, $group_id, $revenue_group_id, $start_date, $end_date) {
    $tables = [
        'head-parish' => [
            'revenue_table' => ['head_parish_revenues', 'other_head_parish_revenues'], // include both for management
            'group_table'   => 'head_parish_revenue_groups',
            'name_table'    => 'head_parish_revenue_groups_map',
            'target_table'  => 'head_parish_revenue_stream_targets',
            'filters'       => ['head_parish_id'],
        ],
        'sub-parish' => [
            'revenue_table' => ['sub_parish_revenues'],
            'group_table'   => 'sub_parish_revenue_groups',
            'name_table'    => 'sub_parish_revenue_groups_map',
            'target_table'  => 'sub_parish_revenue_stream_targets',
            'filters'       => ['head_parish_id','sub_parish_id'],
        ],
        'community' => [
            'revenue_table' => ['community_revenues'],
            'group_table'   => 'community_revenue_groups',
            'name_table'    => 'community_revenue_groups_map',
            'target_table'  => 'community_revenue_stream_targets',
            'filters'       => ['head_parish_id','sub_parish_id','community_id'],
        ],
        'group' => [
            'revenue_table' => ['group_revenues'],
            'group_table'   => 'group_revenue_groups',
            'name_table'    => 'group_revenue_groups_map',
            'target_table'  => 'group_revenue_stream_targets',
            'filters'       => ['head_parish_id','group_id'],
        ]
    ];

    if (!isset($tables[$target])) return null;
    $info = $tables[$target];

    $start_date = date('Y-m-d', strtotime($start_date));
    $end_date   = date('Y-m-d', strtotime($end_date));

    // --- Build management filter values & types (no alias here) ---
    $mgmtCols = [];     // e.g. ['head_parish_id','sub_parish_id']
    $mgmtParams = [];   // values in same order
    $mgmtTypes = '';    // types string for bind_param
    foreach ($info['filters'] as $col) {
        $mgmtCols[] = $col;
        switch ($col) {
            case 'head_parish_id': $mgmtParams[] = $head_parish_id; $mgmtTypes .= 'i'; break;
            case 'sub_parish_id':  $mgmtParams[] = $sub_parish_id;  $mgmtTypes .= 'i'; break;
            case 'community_id':   $mgmtParams[] = $community_id;   $mgmtTypes .= 'i'; break;
            case 'group_id':       $mgmtParams[] = $group_id;       $mgmtTypes .= 'i'; break;
            default:               $mgmtParams[] = null; $mgmtTypes .= 's'; break;
        }
    }

    // helper to build mgmt clause for a given alias (alias may be '' for no alias)
    $buildMgmtClause = function($alias) use ($mgmtCols) {
        if (empty($mgmtCols)) return '';
        $parts = [];
        $pref = $alias !== '' ? ($alias . '.') : '';
        foreach ($mgmtCols as $c) {
            $parts[] = $pref . $c . " = ?";
        }
        return ' AND ' . implode(' AND ', $parts);
    };

    // --- Fetch revenue streams in the group (name_table contains mapping) ---
    $sqlStreams = "
        SELECT m.revenue_stream_id, s.revenue_stream_name
        FROM {$info['name_table']} m
        JOIN head_parish_revenue_streams s ON m.revenue_stream_id = s.revenue_stream_id
        WHERE m.revenue_group_id = ?
    ";
    $stmtStreams = $conn->prepare($sqlStreams);
    if (!$stmtStreams) return null;
    $stmtStreams->bind_param('i', $revenue_group_id);
    $stmtStreams->execute();
    $resStreams = $stmtStreams->get_result();

    $streams = [];
    $streamIds = [];
    while ($row = $resStreams->fetch_assoc()) {
        $streams[$row['revenue_stream_id']] = [
            'revenue_stream_name' => $row['revenue_stream_name'],
            'total_previous' => 0.0,
            'total_current'  => 0.0,
            'target'         => 0.0,
            'balance'        => 0.0
        ];
        $streamIds[] = (int)$row['revenue_stream_id'];
    }
    $stmtStreams->close();

    if (empty($streamIds)) {
        // still try to get group name (fallback)
        $groupName = 'UNKNOWN';
        $stmtG = $conn->prepare("SELECT revenue_group_name FROM {$info['group_table']} WHERE revenue_group_id = ? LIMIT 1");
        if ($stmtG) {
            $stmtG->bind_param('i', $revenue_group_id);
            $stmtG->execute();
            $gRes = $stmtG->get_result()->fetch_assoc();
            $stmtG->close();
            if ($gRes && !empty($gRes['revenue_group_name'])) $groupName = strtoupper($gRes['revenue_group_name']);
        }
        return [
            'revenue_group_name' => $groupName,
            'total_previous' => 0.0,
            'total_current' => 0.0,
            'total_target' => 0.0,
            'balance' => 0.0,
            'revenues' => []
        ];
    }

    $placeholders = implode(',', array_fill(0, count($streamIds), '?'));

    // --- Calculate previous totals for each revenue table ---
    $year_start = date('Y-01-01', strtotime($start_date));
    foreach ($info['revenue_table'] as $revTable) {
        // alias revenue table as r (so mgmt clause will use alias 'r')
        $mgmtClauseForRevenue = $buildMgmtClause('r'); // uses r.col
        $sqlPrev = "
            SELECT r.revenue_stream_id, SUM(r.revenue_amount) AS total
            FROM $revTable r
            WHERE r.revenue_date >= ? AND r.revenue_date < ?
              AND r.revenue_stream_id IN ($placeholders)
              $mgmtClauseForRevenue
            GROUP BY r.revenue_stream_id
        ";

        // build params: year_start, start_date, streamIds..., mgmtParams...
        $params = array_merge([$year_start, $start_date], $streamIds, $mgmtParams);
        $types  = 'ss' . str_repeat('i', count($streamIds)) . $mgmtTypes;

        $stmtPrev = $conn->prepare($sqlPrev);
        if (!$stmtPrev) continue; // skip this revenue table on prepare error
        // bind dynamically
        $stmtPrev->bind_param($types, ...$params);
        $stmtPrev->execute();
        $resPrev = $stmtPrev->get_result();
        while ($row = $resPrev->fetch_assoc()) {
            $id = (int)$row['revenue_stream_id'];
            $streams[$id]['total_previous'] += (float)$row['total'];
        }
        $stmtPrev->close();
    }

    // --- Calculate current totals ---
    foreach ($info['revenue_table'] as $revTable) {
        $mgmtClauseForRevenue = $buildMgmtClause('r');
        $sqlCurr = "
            SELECT r.revenue_stream_id, SUM(r.revenue_amount) AS total
            FROM $revTable r
            WHERE r.revenue_date BETWEEN ? AND ?
              AND r.revenue_stream_id IN ($placeholders)
              $mgmtClauseForRevenue
            GROUP BY r.revenue_stream_id
        ";

        $params = array_merge([$start_date, $end_date], $streamIds, $mgmtParams);
        $types  = 'ss' . str_repeat('i', count($streamIds)) . $mgmtTypes;

        $stmtCurr = $conn->prepare($sqlCurr);
        if (!$stmtCurr) continue;
        $stmtCurr->bind_param($types, ...$params);
        $stmtCurr->execute();
        $resCurr = $stmtCurr->get_result();
        while ($row = $resCurr->fetch_assoc()) {
            $id = (int)$row['revenue_stream_id'];
            $streams[$id]['total_current'] += (float)$row['total'];
        }
        $stmtCurr->close();
    }

    // --- Fetch revenue targets ---
    // alias target table as t and build clause using 't'
    $mgmtClauseForTarget = $buildMgmtClause('t');
    $sqlTarget = "
        SELECT t.revenue_stream_id, SUM(t.revenue_target_amount) AS target
        FROM {$info['target_table']} t
        WHERE t.revenue_target_start_date <= ? AND t.revenue_target_end_date >= ?
          AND t.revenue_stream_id IN ($placeholders)
          $mgmtClauseForTarget
        GROUP BY t.revenue_stream_id
    ";

    $params = array_merge([$end_date, $start_date], $streamIds, $mgmtParams);
    $types  = 'ss' . str_repeat('i', count($streamIds)) . $mgmtTypes;

    $stmtTarget = $conn->prepare($sqlTarget);
    if ($stmtTarget) {
        $stmtTarget->bind_param($types, ...$params);
        $stmtTarget->execute();
        $resTarget = $stmtTarget->get_result();
        while ($row = $resTarget->fetch_assoc()) {
            $id = (int)$row['revenue_stream_id'];
            $streams[$id]['target'] = (float)$row['target'];
            $streams[$id]['balance'] = $streams[$id]['target'] - ($streams[$id]['total_previous'] + $streams[$id]['total_current']);
        }
        $stmtTarget->close();
    }

    // --- Group name with filters (apply same filters as group-level check) ---
    $groupFilters = ["revenue_group_id = ?"];
    $groupParams = [$revenue_group_id];
    $groupTypes  = 'i';
    foreach ($info['filters'] as $col) {
        $groupFilters[] = "$col = ?";
        switch ($col) {
            case 'head_parish_id': $groupParams[] = $head_parish_id; $groupTypes .= 'i'; break;
            case 'sub_parish_id':  $groupParams[] = $sub_parish_id;  $groupTypes .= 'i'; break;
            case 'community_id':   $groupParams[] = $community_id;   $groupTypes .= 'i'; break;
            case 'group_id':       $groupParams[] = $group_id;       $groupTypes .= 'i'; break;
        }
    }
    $stmtGroup = $conn->prepare("SELECT revenue_group_name FROM {$info['group_table']} WHERE ".implode(" AND ", $groupFilters)." LIMIT 1");
    if ($stmtGroup) {
        $stmtGroup->bind_param($groupTypes, ...$groupParams);
        $stmtGroup->execute();
        $groupRes = $stmtGroup->get_result()->fetch_assoc();
        $stmtGroup->close();
    } else {
        $groupRes = null;
    }

    $groupName = $groupRes ? strtoupper($groupRes['revenue_group_name']) : 'UNKNOWN';

    // --- Totals ---
    $totalPrevious = 0.0;
    $totalCurrent  = 0.0;
    $totalTarget   = 0.0;
    foreach ($streams as $s) {
        $totalPrevious += $s['total_previous'];
        $totalCurrent  += $s['total_current'];
        $totalTarget   += $s['target'];
    }
    $balance = $totalTarget - ($totalPrevious + $totalCurrent);

    return [
        'revenue_group_name' => $groupName,
        'total_previous' => $totalPrevious,
        'total_current'  => $totalCurrent,
        'total_target'   => $totalTarget,
        'balance'        => $balance,
        'revenues'       => array_values($streams)
    ];
}


function getRevenueStreamSummary(
    $conn,
    $target,
    $head_parish_id,
    $sub_parish_id,
    $community_id,
    $group_id,
    $revenue_group_id,
    $revenue_stream_id, // mandatory
    $start_date,
    $end_date
) {
    if (!$revenue_stream_id) return null;

    $tables = [
        'head-parish' => [
            'revenue_table' => ['head_parish_revenues', 'other_head_parish_revenues'],
            'group_table'   => 'head_parish_revenue_groups',
            'map_table'     => 'head_parish_revenue_groups_map',       // maps group -> stream
            'target_table'  => 'head_parish_revenue_stream_targets',
            'filters'       => ['head_parish_id'],
        ],
        'sub-parish' => [
            'revenue_table' => ['sub_parish_revenues'],
            'group_table'   => 'sub_parish_revenue_groups',
            'map_table'     => 'sub_parish_revenue_groups_map',
            'target_table'  => 'sub_parish_revenue_stream_targets',
            'filters'       => ['head_parish_id','sub_parish_id'],
        ],
        'community' => [
            'revenue_table' => ['community_revenues'],
            'group_table'   => 'community_revenue_groups',
            'map_table'     => 'community_revenue_groups_map',
            'target_table'  => 'community_revenue_stream_targets',
            'filters'       => ['head_parish_id','sub_parish_id','community_id'],
        ],
        'group' => [
            'revenue_table' => ['group_revenues'],
            'group_table'   => 'group_revenue_groups',
            'map_table'     => 'group_revenue_groups_map',
            'target_table'  => 'group_revenue_stream_targets',
            'filters'       => ['head_parish_id','group_id'],
        ]
    ];

    if (!isset($tables[$target])) return null;
    $info = $tables[$target];

    // normalize dates (no time component)
    $start_date = date('Y-m-d', strtotime($start_date));
    $end_date   = date('Y-m-d', strtotime($end_date));

    // --- Build management filter columns/values/types (no alias yet) ---
    $mgmtCols = [];
    $mgmtParams = [];
    $mgmtTypes = '';
    foreach ($info['filters'] as $col) {
        $mgmtCols[] = $col;
        switch ($col) {
            case 'head_parish_id': $mgmtParams[] = $head_parish_id; $mgmtTypes .= 'i'; break;
            case 'sub_parish_id':  $mgmtParams[] = $sub_parish_id;  $mgmtTypes .= 'i'; break;
            case 'community_id':   $mgmtParams[] = $community_id;   $mgmtTypes .= 'i'; break;
            case 'group_id':       $mgmtParams[] = $group_id;       $mgmtTypes .= 'i'; break;
            default:               $mgmtParams[] = null; $mgmtTypes .= 's'; break;
        }
    }

    // helper: build mgmt clause for a given alias (alias may be '' for no alias)
    $buildMgmtClause = function($alias) use ($mgmtCols) {
        if (empty($mgmtCols)) return '';
        $parts = [];
        $pref = $alias !== '' ? ($alias . '.') : '';
        foreach ($mgmtCols as $c) {
            $parts[] = $pref . $c . " = ?";
        }
        return ' AND ' . implode(' AND ', $parts);
    };

    // --- 1) Fetch revenue stream name (preferably via mapping to ensure belongs to group) ---
    $streamName = null;
    $stmt = $conn->prepare("
        SELECT s.revenue_stream_name
        FROM {$info['map_table']} m
        JOIN head_parish_revenue_streams s ON m.revenue_stream_id = s.revenue_stream_id
        WHERE m.revenue_group_id = ? AND m.revenue_stream_id = ?
        LIMIT 1
    ");
    if ($stmt) {
        $stmt->bind_param('ii', $revenue_group_id, $revenue_stream_id);
        $stmt->execute();
        $res = $stmt->get_result()->fetch_assoc();
        $stmt->close();
        if ($res && !empty($res['revenue_stream_name'])) {
            $streamName = $res['revenue_stream_name'];
        }
    }

    // fallback: direct lookup if the mapping didn't return a name
    if (!$streamName) {
        $stmt2 = $conn->prepare("SELECT revenue_stream_name FROM head_parish_revenue_streams WHERE revenue_stream_id = ? LIMIT 1");
        if ($stmt2) {
            $stmt2->bind_param('i', $revenue_stream_id);
            $stmt2->execute();
            $r2 = $stmt2->get_result()->fetch_assoc();
            $stmt2->close();
            $streamName = $r2 ? $r2['revenue_stream_name'] : null;
        }
    }

    // --- 2) Find applicable single target (if any) that covers the selected range for this stream ---
    $target_amount = 0.0;
    $target_start_date = $start_date;
    $stmtT = $conn->prepare("
        SELECT revenue_target_amount, revenue_target_start_date AS tstart, revenue_target_end_date AS tend
        FROM {$info['target_table']}
        WHERE revenue_stream_id = ?
          AND revenue_target_start_date <= ?
          AND revenue_target_end_date >= ?
        LIMIT 1
    ");
    if ($stmtT) {
        $stmtT->bind_param('iss', $revenue_stream_id, $end_date, $start_date);
        $stmtT->execute();
        $tRes = $stmtT->get_result()->fetch_assoc();
        $stmtT->close();
        if ($tRes) {
            $target_amount = (float)$tRes['revenue_target_amount'];
            $target_start_date = date('Y-m-d', strtotime($tRes['tstart']));
        }
    }

    // --- 3) Compute previous_total (same-year strictly before start_date), using target_start_date & year start rule ---
    $previous_total = 0.0;
    if (date('m-d', strtotime($start_date)) !== '01-01') {
        $year_start = date('Y-01-01', strtotime($start_date));
        // choose later of year_start and target_start_date
        $previous_start_candidate = $target_start_date ?: $year_start;
        $previous_start = (strtotime($previous_start_candidate) > strtotime($year_start)) ? $previous_start_candidate : $year_start;
        // previous_end is strictly before start_date (we'll use DATE >= previous_start AND DATE < start_date)
        $previous_end_check = date('Y-m-d', strtotime($start_date . ' -1 day'));
        if (strtotime($previous_end_check) >= strtotime($previous_start)) {
            // build mgmt clause with alias r
            $mgmtClauseForRevenue = $buildMgmtClause('r'); // uses r.col
            $sqlPrevParts = [];
            $params = [$previous_start, $start_date, $revenue_stream_id];
            $types  = 'ssi'; // previous_start (s), start_date (s), revenue_stream_id (i)
            // prepend mgmt params (they come after)
            $params = array_merge([$previous_start, $start_date, $revenue_stream_id], $mgmtParams);
            $types  = 'ssi' . $mgmtTypes;

            // sum across all revenue tables (head & other head included where applicable)
            foreach ($info['revenue_table'] as $rt) {
                $sqlPrev = "
                    SELECT SUM(r.revenue_amount) as total_prev
                    FROM $rt r
                    WHERE DATE(r.revenue_date) >= ? AND DATE(r.revenue_date) < ?
                      AND r.revenue_stream_id = ?
                      $mgmtClauseForRevenue
                ";
                $stmtPrev = $conn->prepare($sqlPrev);
                if (!$stmtPrev) continue;
                $stmtPrev->bind_param($types, ...$params);
                $stmtPrev->execute();
                $pRow = $stmtPrev->get_result()->fetch_assoc();
                $stmtPrev->close();
                if ($pRow && $pRow['total_prev']) $previous_total += (float)$pRow['total_prev'];
            }
        }
    }

    // --- 4) Fetch individual revenue rows for the selected range and compute current_total ---
    $revenues = [];
    $current_total = 0.0;
    // build mgmt clause for revenue queries (alias r)
    $mgmtClauseForRevenue = $buildMgmtClause('r');

    foreach ($info['revenue_table'] as $rt) {
        $sqlRows = "
            SELECT r.revenue_id, r.revenue_amount, DATE(r.revenue_date) AS date, r.recorded_by, r.payment_method, r.description, r.recorded_from
            FROM $rt r
            WHERE DATE(r.revenue_date) BETWEEN ? AND ?
              AND r.revenue_stream_id = ?
              $mgmtClauseForRevenue
            ORDER BY r.revenue_date ASC, r.date_recorded ASC
        ";
        // params: start_date, end_date, revenue_stream_id, then mgmtParams...
        $params = array_merge([$start_date, $end_date, $revenue_stream_id], $mgmtParams);
        $types  = 'ssi' . $mgmtTypes;

        $stmtRows = $conn->prepare($sqlRows);
        if (!$stmtRows) continue;
        $stmtRows->bind_param($types, ...$params);
        $stmtRows->execute();
        $rRes = $stmtRows->get_result();
        while ($row = $rRes->fetch_assoc()) {
            $amt = $row['revenue_amount'] !== null ? (float)$row['revenue_amount'] : 0.0;
            $revenues[] = [
                'revenue_id' => isset($row['revenue_id']) ? (int)$row['revenue_id'] : null,
                'date' => $row['date'],
                'amount' => $amt,
                'recorded_by' => isset($row['recorded_by']) ? (int)$row['recorded_by'] : null,
                'payment_method' => isset($row['payment_method']) ? $row['payment_method'] : null,
                'description' => isset($row['description']) ? $row['description'] : null,
                'recorded_from' => isset($row['recorded_from']) ? $row['recorded_from'] : null,
            ];
            $current_total += $amt;
        }
        $stmtRows->close();
    }

    // --- 5) Compute group-level annual target for this stream: sum of targets overlapping selected range ---
    $total_target_for_stream = 0.0;
    $mgmtClauseForTarget = $buildMgmtClause('t'); // alias t for target table
    $sqlTargetSum = "
        SELECT SUM(t.revenue_target_amount) AS total_target
        FROM {$info['target_table']} t
        WHERE t.revenue_target_start_date <= ? AND t.revenue_target_end_date >= ?
          AND t.revenue_stream_id = ?
          $mgmtClauseForTarget
    ";
    $paramsT = array_merge([$end_date, $start_date, $revenue_stream_id], $mgmtParams);
    $typesT  = 'ssi' . $mgmtTypes;
    $stmtTS = $conn->prepare($sqlTargetSum);
    if ($stmtTS) {
        $stmtTS->bind_param($typesT, ...$paramsT);
        $stmtTS->execute();
        $tRow = $stmtTS->get_result()->fetch_assoc();
        $stmtTS->close();
        if ($tRow && $tRow['total_target']) $total_target_for_stream = (float)$tRow['total_target'];
    }

    // --- 6) Group name (apply group-level filters) ---
    $groupFilters = ["revenue_group_id = ?"];
    $groupParams = [$revenue_group_id];
    $groupTypes  = 'i';
    foreach ($info['filters'] as $col) {
        $groupFilters[] = "$col = ?";
        switch ($col) {
            case 'head_parish_id': $groupParams[] = $head_parish_id; $groupTypes .= 'i'; break;
            case 'sub_parish_id':  $groupParams[] = $sub_parish_id;  $groupTypes .= 'i'; break;
            case 'community_id':   $groupParams[] = $community_id;   $groupTypes .= 'i'; break;
            case 'group_id':       $groupParams[] = $group_id;       $groupTypes .= 'i'; break;
        }
    }
    $stmtG = $conn->prepare("SELECT revenue_group_name FROM {$info['group_table']} WHERE " . implode(" AND ", $groupFilters) . " LIMIT 1");
    if ($stmtG) {
        $stmtG->bind_param($groupTypes, ...$groupParams);
        $stmtG->execute();
        $gRes = $stmtG->get_result()->fetch_assoc();
        $stmtG->close();
    } else {
        $gRes = null;
    }
    $groupName = $gRes ? strtoupper($gRes['revenue_group_name']) : 'UNKNOWN';

    // --- 7) Balances ---
    $balance_stream = $total_target_for_stream - ($previous_total + $current_total);

    return [
        'revenue_group_name' => $groupName,
        'total_target' => $total_target_for_stream,
        'total_revenues_before_date' => $previous_total,
        'total_revenues' => $current_total,
        'balance' => $balance_stream,
        'revenue_stream_name' => $streamName,
        'revenue_stream_id' => (int)$revenue_stream_id,
        'revenues' => $revenues // array of {revenue_id, date, amount, recorded_by, payment_method, description, recorded_from}
    ];
}


function getNonContributingMembers($conn, $target, $harambee_id, $head_parish_id, $community_id) {
    // Determine contribution table
    $contribution_table = '';
    switch ($target) {
        case 'head-parish': $contribution_table = 'head_parish_harambee_contribution'; break;
        case 'sub-parish': $contribution_table = 'sub_parish_harambee_contribution'; break;
        case 'community': $contribution_table = 'community_harambee_contribution'; break;
        case 'group': $contribution_table = 'groups_harambee_contribution'; break;
        case 'groups': $contribution_table = 'groups_harambee_contribution'; break;
        default: return ["success" => false, "message" => "Invalid target type"];
    }

    // Step 1: Get all active member IDs in the community
    $community_member_ids = [];
    $stmt = $conn->prepare("SELECT member_id FROM church_members WHERE community_id = ? AND head_parish_id = ? AND status = 'Active' AND (type IS NULL OR type != 'Mgeni')");
    $stmt->bind_param("ii", $community_id, $head_parish_id);
    $stmt->execute();
    $res = $stmt->get_result();
    while ($row = $res->fetch_assoc()) $community_member_ids[] = $row['member_id'];
    $stmt->close();

    if (empty($community_member_ids)) return ["success" => true, "data" => []];

    // Step 2: Get contributed member IDs
    $placeholders = implode(',', array_fill(0, count($community_member_ids), '?'));
    $types = str_repeat('i', count($community_member_ids) + 2);
    $params = array_merge([$harambee_id, $head_parish_id], $community_member_ids);

    $query = "SELECT DISTINCT member_id FROM $contribution_table WHERE harambee_id = ? AND head_parish_id = ? AND member_id IN ($placeholders)";
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
    $contributed_member_ids = [];
    while ($row = $res->fetch_assoc()) $contributed_member_ids[] = $row['member_id'];
    $stmt->close();

    // Step 3: Get excluded member IDs (remove harambee_id from WHERE)
    $excluded_member_ids = [];
    if (!empty($community_member_ids)) {
        $placeholders2 = implode(',', array_fill(0, count($community_member_ids), '?'));
        $types2 = str_repeat('i', count($community_member_ids));
        $stmt = $conn->prepare("SELECT member_id FROM church_member_exclusions WHERE head_parish_id = ? AND member_id IN ($placeholders2)");
        $stmt->bind_param('i' . $types2, $head_parish_id, ...$community_member_ids);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($row = $res->fetch_assoc()) $excluded_member_ids[] = $row['member_id'];
        $stmt->close();
    }

    // Step 4: Filter members
    $filtered_member_ids = array_diff($community_member_ids, $contributed_member_ids, $excluded_member_ids);
    if (empty($filtered_member_ids)) return ["success" => true, "data" => []];

    // Step 5: Get member details
    $member_details = [];
    foreach ($filtered_member_ids as $member_id) {
        $res = getMemberDetails($conn, $member_id);
        if ($row = $res->fetch_assoc()) $member_details[] = $row;
    }

    // Step 6: Sort by first name
    usort($member_details, function($a, $b){
        return strcmp($a['first_name'], $b['first_name']);
    });

    return ["success" => true, "data" => $member_details];
}

function getAttendanceEnvelopeStatistics($conn, $head_parish_id, $current_year, $previous_year, $start_date, $end_date) {
    
    // Helper: get envelope usage total and per sub-parish
    $getEnvelopeStats = function($start, $end) use ($conn, $head_parish_id) {

        // Total unique member envelopes for the entire range
        $stmt = $conn->prepare("
            SELECT COUNT(DISTINCT ec.member_id) AS total_envelopes
            FROM envelope_contribution ec
            WHERE ec.head_parish_id = ? AND ec.contribution_date BETWEEN ? AND ?
        ");
        $stmt->bind_param("iss", $head_parish_id, $start, $end);
        $stmt->execute();
        $total = $stmt->get_result()->fetch_assoc()['total_envelopes'] ?? 0;

        // Average envelopes per date (unique per day) - overall
        $stmtAvg = $conn->prepare("
            SELECT DATE(ec.contribution_date) AS cdate,
                   COUNT(DISTINCT ec.member_id) AS unique_daily
            FROM envelope_contribution ec
            WHERE ec.head_parish_id = ?
              AND ec.contribution_date BETWEEN ? AND ?
            GROUP BY DATE(ec.contribution_date)
        ");
        $stmtAvg->bind_param("iss", $head_parish_id, $start, $end);
        $stmtAvg->execute();
        $avgRes = $stmtAvg->get_result();

        $total_days = 0;
        $sum_unique = 0;

        while ($row = $avgRes->fetch_assoc()) {
            $total_days++;
            $sum_unique += (int)$row['unique_daily'];
        }

        $average_per_day = $total_days > 0 ? (int) floor($sum_unique / $total_days) : 0;

        // Per-sub-parish average: unique per day per sub parish, then average across days that had data for that sub parish
        $stmtSubDaily = $conn->prepare("
            SELECT sp.sub_parish_name,
                   DATE(ec.contribution_date) AS cdate,
                   COUNT(DISTINCT ec.member_id) AS unique_daily
            FROM envelope_contribution ec
            JOIN church_members cm ON cm.member_id = ec.member_id
            JOIN sub_parishes sp ON sp.sub_parish_id = cm.sub_parish_id
            WHERE ec.head_parish_id = ?
              AND ec.contribution_date BETWEEN ? AND ?
            GROUP BY sp.sub_parish_id, DATE(ec.contribution_date)
        ");
        $stmtSubDaily->bind_param("iss", $head_parish_id, $start, $end);
        $stmtSubDaily->execute();
        $subDailyRes = $stmtSubDaily->get_result();

        $sub_sums = [];
        $sub_days = [];

        while ($row = $subDailyRes->fetch_assoc()) {
            $name = $row['sub_parish_name'];
            $count = (int)$row['unique_daily'];
            if (!isset($sub_sums[$name])) {
                $sub_sums[$name] = 0;
                $sub_days[$name] = 0;
            }
            $sub_sums[$name] += $count;
            $sub_days[$name] += 1;
        }

        $per_sub = [];
        foreach ($sub_sums as $name => $sum) {
            $days = $sub_days[$name] ?: 1;
            $per_sub[$name] = (int) floor($sum / $days); // whole number average per active date for that sub parish
        }

        return [
            'total' => $total,
            'average' => $average_per_day, // overall average per active date
            'per_sub_parish' => $per_sub
        ];
    };

    // Helper: get attendance as average per day
    $getAttendance = function($start, $end) use ($conn, $head_parish_id) {
        $stmt = $conn->prepare("
            SELECT DATE(attendance_date) AS att_date,
                   SUM(male_attendance + female_attendance) AS adult_sum,
                   SUM(children_attendance) AS children_sum
            FROM head_parish_attendance
            WHERE head_parish_id = ? AND attendance_date BETWEEN ? AND ?
            GROUP BY DATE(attendance_date)
        ");
        $stmt->bind_param("iss", $head_parish_id, $start, $end);
        $stmt->execute();
        $res = $stmt->get_result();

        $total_adult = 0;
        $total_children = 0;
        $days_count = 0;

        while ($row = $res->fetch_assoc()) {
            $days_count++;
            $total_adult += (int)$row['adult_sum'];
            $total_children += (int)$row['children_sum'];
        }

        $days_count = max($days_count, 1);

        return [
            'adult' => (int) floor($total_adult / $days_count),
            'children' => (int) floor($total_children / $days_count)
        ];
    };

    // Benchmarks
    $stmt = $conn->prepare("
        SELECT adult_reading, child_reading
        FROM head_parish_benchmark
        WHERE head_parish_id = ? LIMIT 1
    ");
    $stmt->bind_param("i", $head_parish_id);
    $stmt->execute();
    $bench = $stmt->get_result()->fetch_assoc();
    $adult_benchmark = $bench['adult_reading'] ?? 0;
    $children_benchmark = $bench['child_reading'] ?? 0;

    // Current year stats
    $current_env = $getEnvelopeStats($start_date, $end_date);
    $current_att = $getAttendance($start_date, $end_date);

    // Previous year stats
    $prev_start = (new DateTime($start_date))->modify('-1 year')->format('Y-m-d');
    $prev_end   = (new DateTime($end_date))->modify('-1 year')->format('Y-m-d');

    $prev_env = $getEnvelopeStats($prev_start, $prev_end);
    $prev_att = $getAttendance($prev_start, $prev_end);

    // no_envelope_benchmark
    $stmt2 = $conn->prepare("
        SELECT COUNT(*) AS total_members
        FROM church_members
        WHERE head_parish_id = ?
          AND type = 'Mwenyeji'
          AND envelope_number IS NOT NULL
          AND envelope_number <> ''
    ");
    $stmt2->bind_param("i", $head_parish_id);
    $stmt2->execute();
    $no_envelope_benchmark = $stmt2->get_result()->fetch_assoc()['total_members'] ?? 0;

    return [
        'current_year' => [
            'actual_adult_attendance' => $current_att['adult'],
            'actual_children_attendance' => $current_att['children'],
            'actual_envelope_usage' => $current_env['total'],
            'average_envelope_usage' => $current_env['average'],
            'per_sub_parish' => $current_env['per_sub_parish']
        ],
        'previous_year' => [
            'actual_adult_attendance' => $prev_att['adult'],
            'actual_children_attendance' => $prev_att['children'],
            'actual_envelope_usage' => $prev_env['total'],
            'average_envelope_usage' => $prev_env['average'],
            'per_sub_parish' => $prev_env['per_sub_parish']
        ],
        'adult_benchmark' => $adult_benchmark,
        'children_benchmark' => $children_benchmark,
        'no_envelope_benchmark' => $no_envelope_benchmark
    ];
}



function getRevenueStatistics($conn, $head_parish_id, $current_year, $previous_year, $start_date, $end_date, $reportType) {

    // Convert dates to DateTime objects for calculations
    $startCurr = new DateTime($start_date);
    $endCurr   = new DateTime($end_date);

    // Previous year dates: shift by 1 year
    $startPrev = (clone $startCurr)->modify('-1 year')->format('Y-m-d');
    $endPrev   = (clone $endCurr)->modify('-1 year')->format('Y-m-d');

    // Fetch all bank accounts and their revenue streams
    $stmt = $conn->prepare("
        SELECT hb.account_id, hb.account_name, rs.revenue_stream_id, rs.revenue_stream_name
        FROM head_parish_bank_accounts hb
        LEFT JOIN head_parish_revenue_streams rs ON rs.account_id = hb.account_id
        WHERE hb.head_parish_id = ?
    ");
    $stmt->bind_param("i", $head_parish_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $accounts = [];
    while ($row = $result->fetch_assoc()) {
        $accounts[$row['account_id']]['name'] = $row['account_name'];
        $accounts[$row['account_id']]['streams'][] = $row['revenue_stream_id'];
    }

    $data = [];

    foreach ($accounts as $account_id => $account) {

        $account_name = $account['name'];

        if (!isset($data[$account_name])) {
            $data[$account_name] = [];
        }

        foreach ($account['streams'] as $stream_id) {

            if (!$stream_id) continue;

            // Current period
            $stmt_curr = $conn->prepare("
                SELECT sub_parish_id, SUM(revenue_amount) AS total
                FROM head_parish_revenues
                WHERE head_parish_id = ? AND revenue_stream_id = ?
                  AND revenue_date BETWEEN ? AND ?
                GROUP BY sub_parish_id
            ");
            $stmt_curr->bind_param("iiss", $head_parish_id, $stream_id, $start_date, $end_date);
            $stmt_curr->execute();
            $curr_result = $stmt_curr->get_result();

            // Previous period
            $stmt_prev = $conn->prepare("
                SELECT sub_parish_id, SUM(revenue_amount) AS total
                FROM head_parish_revenues
                WHERE head_parish_id = ? AND revenue_stream_id = ?
                  AND revenue_date BETWEEN ? AND ?
                GROUP BY sub_parish_id
            ");
            $stmt_prev->bind_param("iiss", $head_parish_id, $stream_id, $startPrev, $endPrev);
            $stmt_prev->execute();
            $prev_result = $stmt_prev->get_result();

            // Budgets (annual)
            $stmt_bud = $conn->prepare("
                SELECT sub_parish_id, revenue_target_amount AS budget,
                       revenue_target_start_date, revenue_target_end_date
                FROM head_parish_annual_revenue_distribution
                WHERE head_parish_id = ?
                  AND bank_account_id = ?
                  AND revenue_target_end_date >= ?
                  AND revenue_target_start_date <= ?
            ");
            $stmt_bud->bind_param("iiss", $head_parish_id, $stream_id, $start_date, $end_date);
            $stmt_bud->execute();
            $bud_result = $stmt_bud->get_result();

            $budgets = [];
            while ($row = $bud_result->fetch_assoc()) {

                $budget = $row['budget'];

                if ($reportType === 'quarterly') {
                    $budget = $budget / 4; // quarterly
                } elseif ($reportType === 'custom') {
                    $budget_start = new DateTime($row['revenue_target_start_date']);
                    $budget_end   = new DateTime($row['revenue_target_end_date']);
                    $days_in_year = $budget_start->diff($budget_end)->days + 1; // inclusive
                    $budget = ($budget / 365) * ($startCurr->diff($endCurr)->days + 1); // proportion by days in range
                } // annual: use full budget, no division

                $budgets[$row['sub_parish_id']] = $budget;
            }

            // Combine all sub parishes from all sources
            $sub_ids = [];
            while ($row = $curr_result->fetch_assoc()) $sub_ids[$row['sub_parish_id']] = true;
            $curr_result->data_seek(0);

            while ($row = $prev_result->fetch_assoc()) $sub_ids[$row['sub_parish_id']] = true;
            $prev_result->data_seek(0);

            foreach ($budgets as $sid => $_) $sub_ids[$sid] = true;

            foreach ($sub_ids as $sub_id => $_) {

                // Name lookup
                $stmt_sp = $conn->prepare("SELECT sub_parish_name FROM sub_parishes WHERE sub_parish_id = ? LIMIT 1");
                $stmt_sp->bind_param("i", $sub_id);
                $stmt_sp->execute();
                $sub_name = $stmt_sp->get_result()->fetch_assoc()['sub_parish_name'] ?? 'Unknown';

                if (!isset($data[$account_name][$sub_name])) {
                    $data[$account_name][$sub_name] = [
                        'PREV' => 0,
                        'CURR' => 0,
                        'BUD'  => 0
                    ];
                }

                // Current totals
                $curr_total = 0;
                $curr_result->data_seek(0);
                while ($row = $curr_result->fetch_assoc()) {
                    if ($row['sub_parish_id'] == $sub_id) $curr_total = $row['total'];
                }

                // Previous totals
                $prev_total = 0;
                $prev_result->data_seek(0);
                while ($row = $prev_result->fetch_assoc()) {
                    if ($row['sub_parish_id'] == $sub_id) $prev_total = $row['total'];
                }

                // Budget
                $bud = $budgets[$sub_id] ?? 0;

                // Aggregate
                $data[$account_name][$sub_name]['CURR'] += (float)$curr_total;
                $data[$account_name][$sub_name]['PREV'] += (float)$prev_total;
                $data[$account_name][$sub_name]['BUD']  += $bud;
            }
        }
    }

    return $data;
}


function getOtherHeadParishRevenueStats($conn, $head_parish_id, $current_year, $previous_year, $start_date, $end_date, $reportType) {

    $startCurr = new DateTime($start_date);
    $endCurr   = new DateTime($end_date);

    // Previous year dates: shift by 1 year
    $startPrev = (clone $startCurr)->modify('-1 year')->format('Y-m-d');
    $endPrev   = (clone $endCurr)->modify('-1 year')->format('Y-m-d');

    // Step 1: Get only revenue streams that actually have data in the period
    $stmt = $conn->prepare("
        SELECT DISTINCT r.revenue_stream_id, s.revenue_stream_name
        FROM other_head_parish_revenues r
        LEFT JOIN head_parish_revenue_streams s ON s.revenue_stream_id = r.revenue_stream_id
        WHERE r.head_parish_id = ? AND r.revenue_date BETWEEN ? AND ?
    ");
    $stmt->bind_param("iss", $head_parish_id, $start_date, $end_date);
    $stmt->execute();
    $result = $stmt->get_result();

    $streams = [];
    while ($row = $result->fetch_assoc()) {
        $streams[$row['revenue_stream_id']] = $row['revenue_stream_name'] ?? 'Unknown Stream';
    }

    $data = [];

    foreach ($streams as $stream_id => $stream_name) {

        // Current period revenue
        $stmt_curr = $conn->prepare("
            SELECT SUM(revenue_amount) AS total
            FROM other_head_parish_revenues
            WHERE head_parish_id = ? AND revenue_stream_id = ? AND revenue_date BETWEEN ? AND ?
        ");
        $stmt_curr->bind_param("iiss", $head_parish_id, $stream_id, $start_date, $end_date);
        $stmt_curr->execute();
        $curr_total = $stmt_curr->get_result()->fetch_assoc()['total'] ?? 0;

        // Previous period revenue
        $stmt_prev = $conn->prepare("
            SELECT SUM(revenue_amount) AS total
            FROM other_head_parish_revenues
            WHERE head_parish_id = ? AND revenue_stream_id = ? AND revenue_date BETWEEN ? AND ?
        ");
        $stmt_prev->bind_param("iiss", $head_parish_id, $stream_id, $startPrev, $endPrev);
        $stmt_prev->execute();
        $prev_total = $stmt_prev->get_result()->fetch_assoc()['total'] ?? 0;

        // Budget for this stream
        $stmt_bud = $conn->prepare("
            SELECT revenue_target_amount, revenue_target_start_date, revenue_target_end_date
            FROM head_parish_revenue_stream_targets
            WHERE revenue_stream_id = ? AND head_parish_id = ?
              AND revenue_target_end_date >= ? AND revenue_target_start_date <= ?
            LIMIT 1
        ");
        $stmt_bud->bind_param("iiss", $stream_id, $head_parish_id, $start_date, $end_date);
        $stmt_bud->execute();
        $bud_row = $stmt_bud->get_result()->fetch_assoc();

        $budget = $bud_row['revenue_target_amount'] ?? 0;

        // Adjust budget based on report type
        if ($reportType === 'quarterly') {
            $budget = $budget / 4;
        } elseif ($reportType === 'custom') {
            $budget_start = new DateTime($bud_row['revenue_target_start_date'] ?? $start_date);
            $budget_end   = new DateTime($bud_row['revenue_target_end_date'] ?? $end_date);
            $budget = ($budget / 365) * ($startCurr->diff($endCurr)->days + 1);
        } // annual: keep full budget

        // Only include if there is revenue or budget
        if ((float)$curr_total > 0 || (float)$prev_total > 0 || (float)$budget > 0) {
            $data[$stream_name] = [
                'PREV' => (float)$prev_total,
                'CURR' => (float)$curr_total,
                'BUD'  => round($budget, 2)
            ];
        }
    }

    return $data;
}


function getAllHeadParishRevenueStats($conn, $head_parish_id, $current_year, $previous_year, $start_date, $end_date, $reportType) {
    $startCurr = new DateTime($start_date);
    $endCurr   = new DateTime($end_date);

    // Previous year dates: shift by 1 year
    $startPrev = (clone $startCurr)->modify('-1 year')->format('Y-m-d');
    $endPrev   = (clone $endCurr)->modify('-1 year')->format('Y-m-d');

    $data = [
        'MITAA' => [],
        'JUMUIYA' => [],
        'IDARA_NA_VIKUNDI' => []
    ];

    // 1. Sub-Parishes (MITAA)
    $stmt = $conn->prepare("SELECT sub_parish_id, sub_parish_name FROM sub_parishes WHERE head_parish_id = ?");
    $stmt->bind_param("i", $head_parish_id);
    $stmt->execute();
    $subs = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    foreach ($subs as $sub) {
        $sub_id = $sub['sub_parish_id'];
        $name   = $sub['sub_parish_name'];

        $stmt_curr = $conn->prepare("
            SELECT SUM(revenue_amount) AS total
            FROM sub_parish_revenues
            WHERE sub_parish_id = ? AND head_parish_id = ? AND revenue_date BETWEEN ? AND ?
        ");
        $stmt_curr->bind_param("iiss", $sub_id, $head_parish_id, $start_date, $end_date);
        $stmt_curr->execute();
        $curr = $stmt_curr->get_result()->fetch_assoc()['total'] ?? 0;

        $stmt_prev = $conn->prepare("
            SELECT SUM(revenue_amount) AS total
            FROM sub_parish_revenues
            WHERE sub_parish_id = ? AND head_parish_id = ? AND revenue_date BETWEEN ? AND ?
        ");
        $stmt_prev->bind_param("iiss", $sub_id, $head_parish_id, $startPrev, $endPrev);
        $stmt_prev->execute();
        $prev = $stmt_prev->get_result()->fetch_assoc()['total'] ?? 0;

        $stmt_bud = $conn->prepare("
            SELECT revenue_target_amount, revenue_target_start_date, revenue_target_end_date
            FROM sub_parish_annual_revenue_targets
            WHERE sub_parish_id = ? AND head_parish_id = ? AND revenue_target_end_date >= ? AND revenue_target_start_date <= ?
            LIMIT 1
        ");
        $stmt_bud->bind_param("iiss", $sub_id, $head_parish_id, $start_date, $end_date);
        $stmt_bud->execute();
        $bud_row = $stmt_bud->get_result()->fetch_assoc();
        $bud = $bud_row['revenue_target_amount'] ?? 0;

        if ($reportType === 'quarterly') {
            $bud = $bud / 4;
        } elseif ($reportType === 'custom') {
            $budget_start = new DateTime($bud_row['revenue_target_start_date'] ?? $start_date);
            $budget_end   = new DateTime($bud_row['revenue_target_end_date'] ?? $end_date);
            $bud = ($bud / 365) * ($startCurr->diff($endCurr)->days + 1);
        } // annual: keep full budget

        if ((float)$curr > 0 || (float)$prev > 0 || (float)$bud > 0) {
            $data['MITAA'][$name] = [
                'PREV' => (float)$prev,
                'CURR' => (float)$curr,
                'BUD'  => round($bud, 2)
            ];
        }
    }

    // 2. Communities (JUMUIYA)
    $stmt = $conn->prepare("SELECT community_id, community_name FROM communities WHERE head_parish_id = ?");
    $stmt->bind_param("i", $head_parish_id);
    $stmt->execute();
    $communities = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    foreach ($communities as $comm) {
        $comm_id = $comm['community_id'];
        $name    = $comm['community_name'];

        $stmt_curr = $conn->prepare("
            SELECT SUM(revenue_amount) AS total
            FROM community_revenues
            WHERE community_id = ? AND head_parish_id = ? AND revenue_date BETWEEN ? AND ?
        ");
        $stmt_curr->bind_param("iiss", $comm_id, $head_parish_id, $start_date, $end_date);
        $stmt_curr->execute();
        $curr = $stmt_curr->get_result()->fetch_assoc()['total'] ?? 0;

        $stmt_prev = $conn->prepare("
            SELECT SUM(revenue_amount) AS total
            FROM community_revenues
            WHERE community_id = ? AND head_parish_id = ? AND revenue_date BETWEEN ? AND ?
        ");
        $stmt_prev->bind_param("iiss", $comm_id, $head_parish_id, $startPrev, $endPrev);
        $stmt_prev->execute();
        $prev = $stmt_prev->get_result()->fetch_assoc()['total'] ?? 0;

        $stmt_bud = $conn->prepare("
            SELECT revenue_target_amount, revenue_target_start_date, revenue_target_end_date
            FROM community_annual_revenue_targets
            WHERE community_id = ? AND head_parish_id = ? AND revenue_target_end_date >= ? AND revenue_target_start_date <= ?
            LIMIT 1
        ");
        $stmt_bud->bind_param("iiss", $comm_id, $head_parish_id, $start_date, $end_date);
        $stmt_bud->execute();
        $bud_row = $stmt_bud->get_result()->fetch_assoc();
        $bud = $bud_row['revenue_target_amount'] ?? 0;

        if ($reportType === 'quarterly') {
            $bud = $bud / 4;
        } elseif ($reportType === 'custom') {
            $budget_start = new DateTime($bud_row['revenue_target_start_date'] ?? $start_date);
            $budget_end   = new DateTime($bud_row['revenue_target_end_date'] ?? $end_date);
            $bud = ($bud / 365) * ($startCurr->diff($endCurr)->days + 1);
        }

        if ((float)$curr > 0 || (float)$prev > 0 || (float)$bud > 0) {
            $data['JUMUIYA'][$name] = [
                'PREV' => (float)$prev,
                'CURR' => (float)$curr,
                'BUD'  => round($bud, 2)
            ];
        }
    }

    // 3. Groups (IDARA NA VIKUNDI)
    $stmt = $conn->prepare("SELECT group_id, group_name FROM groups WHERE head_parish_id = ?");
    $stmt->bind_param("i", $head_parish_id);
    $stmt->execute();
    $groups = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    foreach ($groups as $grp) {
        $grp_id = $grp['group_id'];
        $name   = $grp['group_name'];

        $stmt_curr = $conn->prepare("
            SELECT SUM(revenue_amount) AS total
            FROM group_revenues
            WHERE group_id = ? AND head_parish_id = ? AND revenue_date BETWEEN ? AND ?
        ");
        $stmt_curr->bind_param("iiss", $grp_id, $head_parish_id, $start_date, $end_date);
        $stmt_curr->execute();
        $curr = $stmt_curr->get_result()->fetch_assoc()['total'] ?? 0;

        $stmt_prev = $conn->prepare("
            SELECT SUM(revenue_amount) AS total
            FROM group_revenues
            WHERE group_id = ? AND head_parish_id = ? AND revenue_date BETWEEN ? AND ?
        ");
        $stmt_prev->bind_param("iiss", $grp_id, $head_parish_id, $startPrev, $endPrev);
        $stmt_prev->execute();
        $prev = $stmt_prev->get_result()->fetch_assoc()['total'] ?? 0;

        $stmt_bud = $conn->prepare("
            SELECT revenue_target_amount, revenue_target_start_date, revenue_target_end_date
            FROM group_annual_revenue_targets
            WHERE group_id = ? AND head_parish_id = ? AND revenue_target_end_date >= ? AND revenue_target_start_date <= ?
            LIMIT 1
        ");
        $stmt_bud->bind_param("iiss", $grp_id, $head_parish_id, $start_date, $end_date);
        $stmt_bud->execute();
        $bud_row = $stmt_bud->get_result()->fetch_assoc();
        $bud = $bud_row['revenue_target_amount'] ?? 0;

        if ($reportType === 'quarterly') {
            $bud = $bud / 4;
        } elseif ($reportType === 'custom') {
            $budget_start = new DateTime($bud_row['revenue_target_start_date'] ?? $start_date);
            $budget_end   = new DateTime($bud_row['revenue_target_end_date'] ?? $end_date);
            $bud = ($bud / 365) * ($startCurr->diff($endCurr)->days + 1);
        }

        if ((float)$curr > 0 || (float)$prev > 0 || (float)$bud > 0) {
            $data['IDARA_NA_VIKUNDI'][$name] = [
                'PREV' => (float)$prev,
                'CURR' => (float)$curr,
                'BUD'  => round($bud, 2)
            ];
        }
    }

    return $data;
}


function getDailyHeadParishRevenueStats($conn, $head_parish_id, $target_date)
{
    $data = [];

    // Step 1: Get all sub parishes for this head parish
    $stmt = $conn->prepare("
        SELECT sub_parish_id, sub_parish_name 
        FROM sub_parishes 
        WHERE head_parish_id = ?
    ");
    $stmt->bind_param("i", $head_parish_id);
    $stmt->execute();
    $sub_result = $stmt->get_result();

    $sub_parishes = [];
    while ($row = $sub_result->fetch_assoc()) {
        $sub_parishes[$row['sub_parish_id']] = $row['sub_parish_name'];
    }

    // Step 2: Get envelope contributions on the given date (per sub parish)
    $stmt_env = $conn->prepare("
        SELECT sub_parish_id,
               COUNT(DISTINCT member_id) AS envelope_count,
               SUM(amount) AS envelope_amount
        FROM envelope_contribution
        WHERE head_parish_id = ? AND contribution_date = ?
        GROUP BY sub_parish_id
    ");
    $stmt_env->bind_param("is", $head_parish_id, $target_date);
    $stmt_env->execute();
    $env_result = $stmt_env->get_result();

    $envelope_data = [];
    while ($row = $env_result->fetch_assoc()) {
        $envelope_data[$row['sub_parish_id']] = [
            'count' => (int)$row['envelope_count'],
            'amount' => (float)$row['envelope_amount']
        ];
    }

    // Step 3: Get total parish revenues (per sub parish)
    $stmt_rev = $conn->prepare("
        SELECT sub_parish_id, SUM(revenue_amount) AS total_revenue
        FROM head_parish_revenues
        WHERE head_parish_id = ? AND revenue_date = ?
        GROUP BY sub_parish_id
    ");
    $stmt_rev->bind_param("is", $head_parish_id, $target_date);
    $stmt_rev->execute();
    $rev_result = $stmt_rev->get_result();

    $revenue_data = [];
    while ($row = $rev_result->fetch_assoc()) {
        $revenue_data[$row['sub_parish_id']] = (float)$row['total_revenue'];
    }

    // Step 4: Combine results per sub parish
    foreach ($sub_parishes as $sub_id => $sub_name) {
        $env_count  = $envelope_data[$sub_id]['count'] ?? 0;
        $env_amount = $envelope_data[$sub_id]['amount'] ?? 0.0;
        $total_rev  = $revenue_data[$sub_id] ?? 0.0;

        // Non-envelope revenue = total parish revenue - envelope contributions
        $non_env_amount = $total_rev - $env_amount;
        if ($non_env_amount < 0) $non_env_amount = 0; // prevent negative if mismatch

        // Include only sub parishes with any activity
        if ($env_count > 0 || $env_amount > 0 || $total_rev > 0) {
            $data[$sub_name] = [
                'envelope_count'     => $env_count,
                'envelope_amount'    => $env_amount,
                'all_revenue_amount' => $total_rev,
                'non_envelope_amount'=> $non_env_amount
            ];
        }
    }

    return $data;
}

function getDailyAttendanceStats($conn, $head_parish_id, $target_date)
{
    $data = [
        'attendance_target' => 0,
        'attendance_numbers' => []
    ];

    // Step 1: Get attendance target (adult benchmark) for this head parish
    $stmt = $conn->prepare("
        SELECT adult_reading AS attendance_target
        FROM head_parish_benchmark
        WHERE head_parish_id = ? LIMIT 1
    ");
    $stmt->bind_param("i", $head_parish_id);
    $stmt->execute();
    $target = $stmt->get_result()->fetch_assoc();
    $data['attendance_target'] = (int)($target['attendance_target'] ?? 0);

    // Step 2: Get attendance per mass/service for the given date
    $stmt2 = $conn->prepare("
        SELECT service_number,
               SUM(male_attendance) AS male,
               SUM(female_attendance) AS female
        FROM head_parish_attendance
        WHERE head_parish_id = ? AND attendance_date = ?
        GROUP BY service_number
        ORDER BY service_number
    ");
    $stmt2->bind_param("is", $head_parish_id, $target_date);
    $stmt2->execute();
    $result = $stmt2->get_result();

    while ($row = $result->fetch_assoc()) {
        $service_number = $row['service_number'];
        $data['attendance_numbers'][(string)$service_number] = [
            'male' => (int)$row['male'],
            'female' => (int)$row['female']
        ];
    }

    return $data;
}

?>