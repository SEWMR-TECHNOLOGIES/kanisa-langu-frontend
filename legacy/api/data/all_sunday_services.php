<?php
header('Content-Type: application/json');
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/pdo_connection.php');

try {

    // Query to fetch Sunday services
    $query = "
        SELECT 
            ss.service_id,
            ss.head_parish_id,
            ss.service_date,
            ss.base_scripture_text,
            cc.color_name AS service_color,
            cc.color_code AS service_color_code,
            ss.large_liturgy_page_number,
            ss.small_liturgy_page_number,
            ss.large_antiphony_page_number,
            ss.small_antiphony_page_number,
            ss.large_praise_page_number,
            ss.small_praise_page_number
        FROM sunday_services ss
        JOIN church_colors cc ON ss.service_color_id = cc.color_id
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute();
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response = [];

    foreach ($services as $service) {
        $serviceId = $service['service_id'];
        $headParishId = $service['head_parish_id']; // Extract head_parish_id for each service

        // Fetch service times for this service
        $serviceTimesQuery = "
            SELECT hps.service AS service_number, hps.start_time
            FROM head_parish_services hps
            WHERE hps.head_parish_id = :head_parish_id
        ";
        $serviceTimesStmt = $pdo->prepare($serviceTimesQuery);
        $serviceTimesStmt->execute([
            'head_parish_id' => $headParishId,
        ]);
        
        $serviceTimes = $serviceTimesStmt->fetchAll(PDO::FETCH_ASSOC);

        // Format the time to AM/PM and create a new array
        $formattedServiceTimes = [];
        foreach ($serviceTimes as $time) {
            $startTime = $time['start_time']; // The time from the database, e.g., '14:30:00'
            $formattedTime = date('h:i A', strtotime($startTime)); // Format it to '12:30 PM'
            $formattedServiceTimes[] = [
                'service_number' => $time['service_number'], // Keep the service number
                'start_time' => $formattedTime // Add the formatted time
            ];
        }
        
        // Fetch offerings with revenue stream names
        $offeringsQuery = "
            SELECT r.revenue_stream_id, r.revenue_stream_name
            FROM service_offerings s
            JOIN head_parish_revenue_streams r ON s.revenue_stream_id = r.revenue_stream_id
            WHERE s.service_id = :service_id 
            ORDER BY s.id ASC ASC
        ";
        $offeringsStmt = $pdo->prepare($offeringsQuery);
        $offeringsStmt->execute(['service_id' => $serviceId]);
        $offerings = $offeringsStmt->fetchAll(PDO::FETCH_ASSOC); // Fetch as associative array


        // Fetch songs
        $songsQuery = "
            SELECT ps.song_name
            FROM service_songs ss
            JOIN praise_songs ps ON ss.song_id = ps.song_id
            WHERE ss.service_id = :service_id
        ";
        $songsStmt = $pdo->prepare($songsQuery);
        $songsStmt->execute(['service_id' => $serviceId]);
        $songs = $songsStmt->fetchAll(PDO::FETCH_COLUMN);

        // Fetch scriptures
        $scripturesQuery = "
            SELECT b.book_name_en AS book_name, sc.chapter, sc.starting_verse_number, sc.ending_verse_number
            FROM service_scriptures sc
            JOIN bible b ON sc.book_id = b.book_id
            WHERE sc.service_id = :service_id
        ";
        $scripturesStmt = $pdo->prepare($scripturesQuery);
        $scripturesStmt->execute(['service_id' => $serviceId]);
        $scriptures = $scripturesStmt->fetchAll(PDO::FETCH_ASSOC);

        // Fetch choirs
        $choirsQuery = "
            SELECT sc.service_number, c.choir_name
            FROM service_choirs sc
            JOIN church_choirs c ON sc.choir_id = c.choir_id
            WHERE sc.service_id = :service_id
        ";
        $choirsStmt = $pdo->prepare($choirsQuery);
        $choirsStmt->execute(['service_id' => $serviceId]);
        $choirsData = $choirsStmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_COLUMN);

        // Fetch leaders
        $leadersQuery = "
            SELECT sl.service_number, CONCAT(cl.first_name, ' ', cl.last_name) AS leader_name
            FROM service_leaders sl
            JOIN church_leaders cl ON sl.service_leader_id = cl.leader_id
            WHERE sl.service_id = :service_id
        ";
        $leadersStmt = $pdo->prepare($leadersQuery);
        $leadersStmt->execute(['service_id' => $serviceId]);
        $leaders = $leadersStmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // Fetch preachers
        $preachersQuery = "
            SELECT sp.service_number, CONCAT(cl.first_name, ' ', cl.last_name) AS preacher_name
            FROM service_preachers sp
            JOIN church_leaders cl ON sp.preacher_id = cl.leader_id
            WHERE sp.service_id = :service_id
        ";
        $preachersStmt = $pdo->prepare($preachersQuery);
        $preachersStmt->execute(['service_id' => $serviceId]);
        $preachers = $preachersStmt->fetchAll(PDO::FETCH_KEY_PAIR);

        // Fetch elders
        $eldersQuery = "
            SELECT se.service_number, CONCAT(cl.first_name, ' ', cl.last_name) AS elder_name
            FROM service_elders se
            JOIN church_leaders cl ON se.elder_id = cl.leader_id
            WHERE se.service_id = :service_id
        ";
        $eldersStmt = $pdo->prepare($eldersQuery);
        $eldersStmt->execute(['service_id' => $serviceId]);
        $eldersData = $eldersStmt->fetchAll(PDO::FETCH_GROUP | PDO::FETCH_COLUMN);

        // Build service data
        $response[] = [
            'service_id' => $service['service_id'],
            'service_date' => $service['service_date'],
            'main_text' => $service['base_scripture_text'],
            'service_color' => [
                'name' => $service['service_color'], // Service color name
                'code' => $service['service_color_code'], // Service color code
            ],
            'books_page_numbers' => [
                'small_liturgy' => $service['small_liturgy_page_number'],
                'large_liturgy' => $service['large_liturgy_page_number'],
                'small_antiphony' => $service['small_antiphony_page_number'],
                'large_antiphony' => $service['large_antiphony_page_number'],
                'small_praise' => $service['small_praise_page_number'],
                'large_praise' => $service['large_praise_page_number'],
            ],
            'service_times' => $formattedServiceTimes,
            'offerings' => $offerings,
            'songs' => $songs,
            'scriptures' => $scriptures,
            'choirs' => $choirsData,
            'preacher' => $preachers,
            'leader' => $leaders,
            'elders' => $eldersData
        ];

    }

     // Final response
    echo json_encode([
        'success' => true,
        'sunday_services' => $response,
    ], JSON_PRETTY_PRINT);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
?>
