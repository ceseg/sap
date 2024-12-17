<?php
session_start();

if (empty($_SESSION['B1SESSION'])) {
    http_response_code(401); // Sessão não autorizada
    exit;
}

$sessionId = $_SESSION['B1SESSION'];
$serviceLayerUrl = "https://192.168.0.10:50000/b1s/v1/Items";

// Verifica se CardCode foi passado
if (!isset($_GET['ItemCode'])) {
    http_response_code(400); // Requisição inválida
    echo json_encode(["error" => "Código do Item não fornecido."]);
    exit;
}

$itemCode = $_GET['ItemCode'];
$url = "$serviceLayerUrl('$itemCode')";

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
$result = file_get_contents($url, false, $context);

if ($result === false) {
    http_response_code(500);
    echo json_encode(["error" => "Erro ao buscar o Nome do Item."]);
    exit;
}

$response = json_decode($result, true);

if (isset($response['ItemName'])) {
    echo json_encode(["ItemName" => $response['ItemName']]);
} else {
    http_response_code(404);
    echo json_encode(["error" => "Item não encontrado."]);
}
?>
