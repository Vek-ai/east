<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $lat = $_POST['lat'] ?? null;
    $lng = $_POST['lng'] ?? null;

    if ($lat === null || $lng === null) {
        echo json_encode(['error' => 'Missing coordinates']);
        exit;
    }

    $url = "https://nominatim.openstreetmap.org/reverse?lat=$lat&lon=$lng&format=json&addressdetails=1";

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_USERAGENT, 'MetalApp/1.0 (kentuckymetaleast@gmail.com)');

    $response = curl_exec($ch);
    $error = curl_error($ch);
    curl_close($ch);

    header('Content-Type: application/json');

    if ($response) {
        $data = json_decode($response, true);
        echo json_encode(['display_name' => $data['display_name'] ?? '']);
    } else {
        echo json_encode(['display_name' => '', 'error' => $error]);
    }
    exit;
}
?>