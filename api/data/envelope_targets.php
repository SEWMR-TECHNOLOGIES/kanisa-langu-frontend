<?php
header('Content-Type: application/json');
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/db_connection.php');
require_once($_SERVER['DOCUMENT_ROOT'] . '/utils/helpers.php');

if (!isset($_SESSION['head_parish_id'])) {
    echo json_encode(["success" => false, "message" => "Head Parish ID is missing from session"]);
    exit();
}

$head_parish_id = $_SESSION['head_parish_id'];

if ($conn->connect_error) {
    echo json_encode(["success" => false, "message" => "Database connection failed: " . $conn->connect_error]);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $year = isset($_GET['year']) ? intval($_GET['year']) : null;

    if (!$year) {
        echo json_encode(["success" => false, "message" => "Year is required."]);
        exit();
    }

    $limit = isset($_GET['limit']) ? ($_GET['limit'] === 'all' ? PHP_INT_MAX : intval($_GET['limit'])) : 10;
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $offset = ($page - 1) * $limit;

    $searchQuery = isset($_GET['query']) ? $conn->real_escape_string($_GET['query']) : '';

    $sql = "
        SELECT m.member_id AS id, m.first_name, m.middle_name, m.last_name, 
               DATE_FORMAT(m.date_of_birth, '%d-%m-%Y') AS date_of_birth, 
               t.name AS title, o.occupation_name, 
               sp.sub_parish_name, c.community_name, m.type, m.envelope_number, m.phone,
               COALESCE(et.target, 0) AS target, et.from_date, et.end_date
        FROM church_members m
        LEFT JOIN envelope_targets et ON m.member_id = et.member_id
        LEFT JOIN titles t ON m.title_id = t.id
        LEFT JOIN occupations o ON m.occupation_id = o.occupation_id
        LEFT JOIN sub_parishes sp ON m.sub_parish_id = sp.sub_parish_id
        LEFT JOIN communities c ON m.community_id = c.community_id
        WHERE m.head_parish_id = $head_parish_id
          AND (YEAR(et.from_date) = $year OR YEAR(et.end_date) = $year)";
    
    if (!empty($searchQuery)) {
        $sql .= " AND (
                      CONCAT(m.first_name, ' ', m.middle_name, ' ', m.last_name) LIKE '%$searchQuery%' 
                      OR m.first_name LIKE '%$searchQuery%' 
                      OR m.middle_name LIKE '%$searchQuery%' 
                      OR m.last_name LIKE '%$searchQuery%' 
                      OR m.phone LIKE '%$searchQuery%' 
                      OR m.envelope_number LIKE '%$searchQuery%'
                  )";
    }


    $sql .= "
        UNION
        SELECT m.member_id AS id, m.first_name, m.middle_name, m.last_name, 
               DATE_FORMAT(m.date_of_birth, '%d-%m-%Y') AS date_of_birth, 
               t.name AS title, o.occupation_name, 
               sp.sub_parish_name, c.community_name, m.type, m.envelope_number, m.phone,
               0 AS target, NULL AS from_date, NULL AS end_date
        FROM church_members m
        LEFT JOIN envelope_contribution ec ON m.member_id = ec.member_id
        LEFT JOIN titles t ON m.title_id = t.id
        LEFT JOIN occupations o ON m.occupation_id = o.occupation_id
        LEFT JOIN sub_parishes sp ON m.sub_parish_id = sp.sub_parish_id
        LEFT JOIN communities c ON m.community_id = c.community_id
        WHERE m.head_parish_id = $head_parish_id
          AND ec.member_id IS NOT NULL
          AND m.member_id NOT IN (
              SELECT member_id 
              FROM envelope_targets 
              WHERE YEAR(from_date) = $year OR YEAR(end_date) = $year
          )";
    
    if (!empty($searchQuery)) {
        $sql .= " AND (
                      CONCAT(m.first_name, ' ', m.middle_name, ' ', m.last_name) LIKE '%$searchQuery%' 
                      OR m.first_name LIKE '%$searchQuery%' 
                      OR m.middle_name LIKE '%$searchQuery%' 
                      OR m.last_name LIKE '%$searchQuery%' 
                      OR m.phone LIKE '%$searchQuery%' 
                      OR m.envelope_number LIKE '%$searchQuery%'
                  )";
    }

    
    $sql .= " LIMIT $limit OFFSET $offset";
    
    
        $result = $conn->query($sql);
    
        if ($result) {
            $members = [];
            while ($row = $result->fetch_assoc()) {
                $row['target'] = number_format($row['target'], 0);
                $row['id'] = encryptData($row['id']);
                $members[] = $row;
            }
    
            $countSql = "
        SELECT COUNT(*) AS total
        FROM (
            SELECT m.member_id
            FROM church_members m
            LEFT JOIN envelope_targets et ON m.member_id = et.member_id
            WHERE m.head_parish_id = $head_parish_id
              AND (YEAR(et.from_date) = $year OR YEAR(et.end_date) = $year)";
            
    if (!empty($searchQuery)) {
        $countSql .= " AND (
                          CONCAT(m.first_name, ' ', m.middle_name, ' ', m.last_name) LIKE '%$searchQuery%' 
                          OR m.first_name LIKE '%$searchQuery%' 
                          OR m.middle_name LIKE '%$searchQuery%' 
                          OR m.last_name LIKE '%$searchQuery%' 
                          OR m.phone LIKE '%$searchQuery%' 
                          OR m.envelope_number LIKE '%$searchQuery%'
                      )";
    }

    
    $countSql .= "
            UNION
            SELECT m.member_id
            FROM church_members m
            LEFT JOIN envelope_contribution ec ON m.member_id = ec.member_id
            WHERE m.head_parish_id = $head_parish_id
              AND ec.member_id IS NOT NULL
              AND m.member_id NOT IN (
                  SELECT member_id 
                  FROM envelope_targets 
                  WHERE YEAR(from_date) = $year OR YEAR(end_date) = $year
              )";
    
    if (!empty($searchQuery)) {
        $countSql .= " AND (
                          CONCAT(m.first_name, ' ', m.middle_name, ' ', m.last_name) LIKE '%$searchQuery%' 
                          OR m.first_name LIKE '%$searchQuery%' 
                          OR m.middle_name LIKE '%$searchQuery%' 
                          OR m.last_name LIKE '%$searchQuery%' 
                          OR m.phone LIKE '%$searchQuery%' 
                          OR m.envelope_number LIKE '%$searchQuery%'
                      )";
    }

    
    $countSql .= ") AS combined";


        $countResult = $conn->query($countSql);
        $totalRecords = $countResult->fetch_assoc()['total'];
        $totalPages = ceil($totalRecords / $limit);

        echo json_encode([
            "success" => true,
            "data" => $members,
            "total_pages" => $totalPages,
            "current_page" => $page
        ]);
    } else {
        echo json_encode(["success" => false, "message" => "Failed to fetch members: " . $conn->error]);
    }
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}

$conn->close();
?>
