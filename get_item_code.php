<?php
session_start();
if (empty($_SESSION['B1SESSION'])) {
    header('Content-Type: application/json');
    echo json_encode(["error" => "Sessão não encontrada"]);
    exit;
}

$sessionId = $_SESSION['B1SESSION'];
$sww = isset($_GET['SWW']) ? $_GET['SWW'] : '';

if (!$sww) {
    echo json_encode(["error" => "SWW não fornecido"]);
    exit;
}

$serviceLayerUrl = "https://192.168.0.10:50000/b1s/v1/Items?\$filter=U_SWW eq '$sww'";

$headers = [
    "Content-Type: application/json",
    "Cookie: B1SESSION=$sessionId"
];

$options = [
    'http' => [
        'header' => implode("\r\n", $headers),
        'method' => 'GET'
    ],
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false
    ]
];

$context = stream_context_create($options);

try {
    $result = file_get_contents($serviceLayerUrl, false, $context);
    $responseHeaders = $http_response_header;
    $httpStatus = explode(' ', $responseHeaders[0])[1];

    if ($result === false || $httpStatus >= 400) {
        $error = error_get_last();
        throw new Exception("Erro na comunicação com o Service Layer: HTTP $httpStatus. " . $error['message']);
    }

    $response = json_decode($result, true);
    if (isset($response['value'][0]['ItemCode'])) {
        echo json_encode([
            "ItemCode" => $response['value'][0]['ItemCode'],
            "ItemName" => $response['value'][0]['ItemName']
        ]);
    } else {
        echo json_encode(["error" => "Item não encontrado"]);
    }
} catch (Exception $e) {
    echo json_encode(["error" => $e->getMessage()]);
}
?>
