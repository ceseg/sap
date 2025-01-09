<?php
session_start();
if (empty($_SESSION['B1SESSION'])) {
    header('Location: login.php');
    exit;
}

// Configurações iniciais
$sessionId = $_SESSION['B1SESSION'];
$serviceLayerUrl = "https://192.168.0.10:50000/b1s/v1/";

// Obter o identificador da troca
$code = isset($_GET['Code']) ? $_GET['Code'] : '';

if (!empty($code)) {
    $url = $serviceLayerUrl . "U_TROCAS('$code')";
    $headers = [
        "Content-Type: application/json",
        "Cookie: B1SESSION=$sessionId"
    ];

    $options = [
        'http' => [
            'header' => implode("\r\n", $headers),
            'method' => 'DELETE',
            'ignore_errors' => true
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false
        ]
    ];

    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);

    if ($result !== false) {
        header('Location: index.php');
        exit;
    } else {
        echo "Erro ao excluir troca.";
    }
}
?>
