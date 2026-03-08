<?php

$revenues = [
    ["id" => 1, "name" => "Revenue A", "items" => [
        ["id" => 101, "name" => "Item A1", "value" => 2000],
        ["id" => 102, "name" => "Item A2", "value" => 1500],
        ["id" => 103, "name" => "Item A3", "value" => 1800],
        ["id" => 104, "name" => "Item A4", "value" => 2200],
        ["id" => 105, "name" => "Item A5", "value" => 2500]
    ]],
    ["id" => 2, "name" => "Revenue B", "items" => [
        ["id" => 201, "name" => "Item B1", "value" => 3000],
        ["id" => 202, "name" => "Item B2", "value" => 2800],
        ["id" => 203, "name" => "Item B3", "value" => 2500],
        ["id" => 204, "name" => "Item B4", "value" => 2700],
        ["id" => 205, "name" => "Item B5", "value" => 4000]
    ]],
    ["id" => 3, "name" => "Revenue C", "items" => [
        ["id" => 301, "name" => "Item C1", "value" => 2000],
        ["id" => 302, "name" => "Item C2", "value" => 2200]
    ]],
];

// Function to print the revenue table dynamically while calculating row-wise total
function printRevenueTable($revenues, $itemsPerBatch = 3)
{
    // Get the max number of items in any revenue
    $maxItems = max(array_map(fn($rev) => count($rev['items']), $revenues));

    echo "<table border='1' cellpadding='5' cellspacing='0'>";
    echo "<tr><th>S/N</th><th>Revenue Name</th>";

    // Always print exactly 3 item columns
    for ($i = 0; $i < $itemsPerBatch; $i++) {
        echo "<th>Item " . ($i + 1) . "</th>";
    }
    echo "<th>Total</th></tr>";

    // Loop through revenues and print rows in batches
    for ($start = 0; $start < $maxItems; $start += $itemsPerBatch) {
        foreach ($revenues as $index => $revenue) {
            $rowTotal = 0;  // Initialize row total to 0
            echo "<tr>";
            echo "<td>" . ($index + 1) . "</td>";
            echo "<td>" . $revenue['name'] . "</td>";

            // Print exactly 3 item columns (fill empty if needed)
            for ($i = 0; $i < $itemsPerBatch; $i++) {
                $itemIndex = $start + $i;
                if (isset($revenue['items'][$itemIndex])) {
                    $item = $revenue['items'][$itemIndex];
                    echo "<td>" . $item['name'] . " (" . $item['value'] . ")</td>";
                    $rowTotal += $item['value'];  // Add item value to row total
                } else {
                    echo "<td></td>";  // Keep column structure intact
                }
            }

            // Print the calculated row total
            echo "<td>" . $rowTotal . "</td>";
            echo "</tr>";
        }
    }

    echo "</table>";
}

// Call the function to print the revenue table
printRevenueTable($revenues);

?>
