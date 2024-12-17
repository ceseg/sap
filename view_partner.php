<?php
session_start();
if (empty($_SESSION['B1SESSION'])) {
    header('Location: login.php');
    exit;
}

$sessionId = $_SESSION['B1SESSION'];
$serviceLayerUrl = "https://192.168.0.10:50000/b1s/v1/";
$cardCode = $_GET['CardCode'] ?? '';

// Verificar se o CardCode foi fornecido
if (empty($cardCode)) {
    echo "Código do parceiro não fornecido.";
    exit;
}

// Função para buscar os detalhes do Parceiro de Negócios
function getBusinessPartnerDetails($serviceLayerUrl, $sessionId, $cardCode) {
    $url = $serviceLayerUrl . "BusinessPartners('$cardCode')";

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
            throw new Exception("Erro na comunicação com o Service Layer.");
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

// Buscar detalhes do Parceiro
$partnerDetails = getBusinessPartnerDetails($serviceLayerUrl, $sessionId, $cardCode);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detalhes do Parceiro</title>
    <link href="./css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container my-4">
        <h1 class="text-center mb-4">Detalhes do Parceiro</h1>

        <?php if ($partnerDetails): ?>
            <div class="card">
                <div class="card-body">
                    <h5 class="card-title"><?= htmlspecialchars($partnerDetails['CardName']) ?></h5>
                    <p class="card-text">
                        <strong>Código:</strong> <?= htmlspecialchars($partnerDetails['CardCode']) ?><br>
                        <strong>Endereço:</strong> <?= htmlspecialchars($partnerDetails['Address']) ?><br>
                        <strong>Email:</strong> <?= htmlspecialchars($partnerDetails['EmailAddress']) ?><br>
                        <strong>Telefone:</strong> <?= htmlspecialchars($partnerDetails['Phone1']) ?>
                    </p>
                </div>
                <!-- Na página de detalhes do parceiro de negócios -->
<a href="trocas.php?CardCode=<?= urlencode($partnerDetails['CardCode']) ?>&CardName=<?= urlencode($partnerDetails['CardName']) ?>" class="btn btn-warning btn-sm">Registrar Troca</a>

            </div>
        <?php else: ?>
            <p class="text-danger text-center">Erro ao buscar os detalhes do parceiro.</p>
        <?php endif; ?>
        

        <a href="parceiros.php" class="btn btn-primary mt-4">Voltar</a>
    </div>
</body>
</html>
