<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lat = $_POST['lat'] ?? null;
    $lng = $_POST['lng'] ?? null;

    if ($lat === null || $lng === null) {
        echo json_encode(['error' => 'Missing coordinates']);
        exit;
    }

    $url = "https://nominatim.openstreetmap.org/reverse?lat=$lat&lon=$lng&format=json&addressdetails=1";

    $opts = [
        "http" => [
            "method" => "GET",
            "header" => "User-Agent: Metal/1.0\r\n"
        ]
    ];

    $context = stream_context_create($opts);
    $response = @file_get_contents($url, false, $context);

    header('Content-Type: application/json');

    if ($response) {
        $data = json_decode($response, true);
        echo json_encode(['display_name' => $data['display_name'] ?? '']);
    } else {
        echo json_encode(['display_name' => '']);
    }
    exit;
}
?>
