<?php
header('Content-Type: application/json');
session_start();

require_once($_SERVER['DOCUMENT_ROOT'] . '/config/pdo_connection.php');

try {
    // Check if the request method is POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        echo json_encode(['success' => false, 'message' => 'Invalid request method. Use POST.']);
        exit;
    }

    // Extract 'head_parish_id' from the POST variables
    $headParishId = isset($_POST['head_parish_id']) ? (int) $_POST['head_parish_id'] : 0;
    
    if ($headParishId <= 0) {
        echo json_encode(['success' => false, 'message' => 'Invalid or missing head_parish_id.']);
        exit;
    }
    
    // Query to fetch Sunday services filtered by head_parish_id
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
        WHERE ss.head_parish_id = :head_parish_id
        ORDER BY ss.service_date DESC
    ";

    $stmt = $pdo->prepare($query);
    $stmt->execute(['head_parish_id' => $headParishId]);
    $services = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $response = [];

    foreach ($services as $service) {
        $serviceId = $service['service_id'];
        $headParishId = $service['head_parish_id']; // Extract head_parish_id for each service

        // Fetch all service numbers and times from head_parish_services for the given head_parish_id
        $serviceTimesQuery = "
            SELECT hps.service AS service_number, hps.start_time
            FROM head_parish_services hps
            WHERE hps.head_parish_id = :head_parish_id
        ";
        $serviceTimesStmt = $pdo->prepare($serviceTimesQuery);
        $serviceTimesStmt->execute(['head_parish_id' => $headParishId]);
        
        $serviceTimes = $serviceTimesStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Initialize the result array to store formatted service times
        $formattedServiceTimes = [];
        
        foreach ($serviceTimes as $time) {
            // For each service number, check if the time exists in sunday_service_times
            $checkServiceTimeQuery = "
                SELECT time
                FROM sunday_service_times
                WHERE service_id = :service_id AND service_number = :service_number
            ";
            $checkServiceTimeStmt = $pdo->prepare($checkServiceTimeQuery);
            $checkServiceTimeStmt->execute([
                'service_id' => $serviceId, // The ID of the service from the request
                'service_number' => $time['service_number'] // The service number
            ]);
        
            $existingServiceTime = $checkServiceTimeStmt->fetch(PDO::FETCH_ASSOC);
        
            // If service time exists in sunday_service_times, use it
            if ($existingServiceTime) {
                $startTime = $existingServiceTime['time']; // Use the time from sunday_service_times
            } else {
                // Use the time from head_parish_services if no time is set in sunday_service_times
                $startTime = $time['start_time'];
            }
        
            // Format the time to AM/PM
            $formattedTime = date('h:i A', strtotime($startTime));
        
            // Store the formatted time in the result array
            $formattedServiceTimes[] = [
                'service_number' => (int) $time['service_number'], // Keep the service number
                'start_time' => $formattedTime // Add the formatted time
            ];
        }
        
        // Fetch offerings with revenue stream names
        $offeringsQuery = "
            SELECT r.revenue_stream_id, r.revenue_stream_name
            FROM service_offerings s
            JOIN head_parish_revenue_streams r ON s.revenue_stream_id = r.revenue_stream_id
            WHERE s.service_id = :service_id
        ";
        $offeringsStmt = $pdo->prepare($offeringsQuery);
        $offeringsStmt->execute(['service_id' => $serviceId]);
        $offeringsRaw = $offeringsStmt->fetchAll(PDO::FETCH_ASSOC);
        $offerings = [];
        
        foreach ($offeringsRaw as $offering) {
            $offerings[] = [
                'revenue_stream_id' => (int) $offering['revenue_stream_id'],
                'revenue_stream_name' => $offering['revenue_stream_name'],
            ];
        }


       // Fetch songs along with song number
        $songsQuery = "
            SELECT ps.song_number, ps.song_name
            FROM service_songs ss
            JOIN praise_songs ps ON ss.song_id = ps.song_id
            WHERE ss.service_id = :service_id ORDER BY ss.id
        ";
        $songsStmt = $pdo->prepare($songsQuery);
        $songsStmt->execute(['service_id' => $serviceId]);
        $songs = $songsStmt->fetchAll(PDO::FETCH_ASSOC); // Fetch as associative array
        
        // Format the songs with both song number and song name
        $formattedSongs = [];
        foreach ($songs as $song) {
            $formattedSongs[] = [
                'song_number' => (int) $song['song_number'],  // Add song number
                'song_name' => $song['song_name']      // Add song name
            ];
        }


        // Fetch scriptures
        $scripturesQuery = "
            SELECT b.book_name_sw AS book_name, sc.chapter, sc.starting_verse_number, sc.ending_verse_number
            FROM service_scriptures sc
            JOIN bible b ON sc.book_id = b.book_id
            WHERE sc.service_id = :service_id ORDER BY sc.id
        ";
        $scripturesStmt = $pdo->prepare($scripturesQuery);
        $scripturesStmt->execute(['service_id' => $serviceId]);
        $scriptures = $scripturesStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Cast numeric fields to int explicitly
        foreach ($scriptures as &$scripture) {
            $scripture['chapter'] = (int) $scripture['chapter'];
            $scripture['starting_verse_number'] = (int) $scripture['starting_verse_number'];
            // ending_verse_number may be nullable, so check before casting
            if ($scripture['ending_verse_number'] !== null) {
                $scripture['ending_verse_number'] = (int) $scripture['ending_verse_number'];
            }
        }
        unset($scripture); // break reference


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
            'service_id' => (int) $service['service_id'],
            'service_date' => $service['service_date'],
            'main_text' => strtoupper($service['base_scripture_text']),
            'service_color' => [
                'name' => $service['service_color'], // Service color name
                'code' => $service['service_color_code'], // Service color code
            ],
            'books_page_numbers' => [
                'small_liturgy' => (int)$service['small_liturgy_page_number'],
                'large_liturgy' => (int)$service['large_liturgy_page_number'],
                'small_antiphony' => (int)$service['small_antiphony_page_number'],
                'large_antiphony' => (int)$service['large_antiphony_page_number'],
                'small_praise' => (int)$service['small_praise_page_number'],
                'large_praise' => (int)$service['large_praise_page_number'],
            ],
            'service_times' => $formattedServiceTimes,
            'offerings' => $offerings,
            'songs' => $formattedSongs,
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
    ]);

} catch (PDOException $e) {
    echo json_encode(["success" => false, "message" => "Database error: " . $e->getMessage()]);
}
?>
