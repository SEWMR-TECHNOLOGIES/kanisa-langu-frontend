<?php
header('Content-Type: application/json');
session_start();

// Define the initial year constant
define('INITIAL_YEAR', 2024);

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Determine the current year
    $currentYear = intval(date('Y'));
    
    // Initialize an array to hold the envelope data
    $envelopes = [];
    
    // Loop through years from INITIAL_YEAR to the current year
    for ($year = INITIAL_YEAR; $year <= $currentYear; $year++) {
        $envelopes[] = [
            'name' => 'Bahasha',
            'description' => 'Sadaka ya bahasha ya Shukrani Yangu kwa Bwana',
            'year' => $year,
            'from_date' => "$year-01-01",
            'to_date' => "$year-12-31",
        ];
    }

    // Reverse the array to ensure the current year is at the top
    $envelopes = array_reverse($envelopes);
    
    // Return the envelope data as a JSON response
    echo json_encode([
        "success" => true,
        "message" => "Envelope data generated successfully",
        "envelopes" => $envelopes
    ]);
} else {
    echo json_encode(["success" => false, "message" => "Invalid request method"]);
}
?>
