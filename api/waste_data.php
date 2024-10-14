<?php
include 'db.php';

// Fetch waste data grouped by wasteType
$sql_types = "SELECT wasteType, SUM(wasteWeight) AS total FROM waste GROUP BY wasteType";
$result_types = $conn->query($sql_types);
$waste_types = [];
while ($row = $result_types->fetch_assoc()) {
    $waste_types[] = $row;
}

// Fetch waste data grouped by wasteDate (for the line chart)
$sql_daily = "SELECT wasteDate AS day, SUM(wasteWeight) AS total FROM waste GROUP BY wasteDate ORDER BY wasteDate";
$result_daily = $conn->query($sql_daily);
$daily_waste = [];
while ($row = $result_daily->fetch_assoc()) {
    $daily_waste[] = $row;
}

// Output the data in JSON format
header('Content-Type: application/json');
echo json_encode([
    'waste_types' => $waste_types,
    'daily_waste' => $daily_waste
]);
?>
