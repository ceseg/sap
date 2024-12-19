<?php
session_start();
if (empty($_SESSION['B1SESSION'])) {
    header('Location: login.php');
    exit;
}

$sessionId = $_SESSION['B1SESSION'];
$serviceLayerUrl = "https://192.168.0.10:50000/b1s/v1/";
$itemCode = $_GET['ItemCode'] ?? '';

if (empty($itemCode)) {
    echo "Código do item não fornecido.";
    exit;
}

// Função para buscar detalhes do item
function getItemDetails($serviceLayerUrl, $sessionId, $itemCode) {
    $url = $serviceLayerUrl . "Items('" . urlencode($itemCode) . "')";
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
        $result = file_get_contents($url, false, $context);

        if ($result === false) {
            throw new Exception("Erro ao buscar detalhes do item.");
        }

        $response = json_decode($result, true);
        if (isset($response['error'])) {
            throw new Exception("Erro do Service Layer: " . $response['error']['message']['value']);
        }

        return $response;
    } catch (Exception $e) {
        error_log($e->getMessage());
        return null;
    }
}

$itemDetails = getItemDetails($serviceLayerUrl, $sessionId, $itemCode);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Item</title>
    <link href="./css/bootstrap.min.css" rel="stylesheet">
    <style>
        body    {
            background-color:rgba(104, 213, 235, 0.6);
        }
    </style>
</head>
<body>
    <div class="container my-4">
        <h1 class="text-center">Detalhes do Item</h1>
        <?php if ($itemDetails): ?>
            <ul class="list-group mt-4">
                <li class="list-group-item"><strong>Código:</strong> <?= htmlspecialchars($itemDetails['ItemCode']) ?></li>
                <li class="list-group-item"><strong>Referência:</strong> <?= htmlspecialchars($itemDetails['SWW']) ?></li>
                <li class="list-group-item"><strong>Nome:</strong> <?= htmlspecialchars($itemDetails['ItemName']) ?></li>
                <li class="list-group-item"><strong>Grupo:</strong> <?= htmlspecialchars($itemDetails['ItemsGroupCode'] ?? 'N/A') ?></li>
                <li class="list-group-item"><strong>Unidade de Medida:</strong> <?= htmlspecialchars($itemDetails['InventoryUOM'] ?? 'N/A') ?></li>
                <li class="list-group-item"><strong>Estoque:</strong> <?= htmlspecialchars($itemDetails['OnHand'] ?? 'N/A') ?></li>
            </ul>
        <?php else: ?>
            <div class="alert alert-danger mt-4">Não foi possível carregar os detalhes do item.</div>
        <?php endif; ?>
        <a href="itens.php" class="btn btn-primary mt-4">Voltar</a>
    </div>
    <b>
        <p align="center">Desenvolvido por Alumínio Ramos</p>
    </b>
</body>
</html>
