<?php
session_start();
if (empty($_SESSION['B1SESSION'])) {
    header('Location: login.php');
    exit;
}

$sessionId = $_SESSION['B1SESSION'];
$serviceLayerUrl = "https://192.168.0.10:50000/b1s/v1/";

$cardCode = isset($_GET['CardCode']) ? $_GET['CardCode'] : '';

if ($_SERVER['REQUEST_METHOD'] === 'PATCH') {
    // Recuperando os dados do formulário
    $itemCode = $_POST['ItemCode'];
    $quantity = $_POST['Quantity'];
    $exchangeDate = $_POST['ExchangeDate'];
    $reason = $_POST['Reason'];

    // Dados para criar a troca no SAP Business One
    $exchangeData = [
        'CardCode' => $cardCode,
        'ItemCode' => $itemCode,
        'Quantity' => $quantity,
        'ExchangeDate' => $exchangeDate,
        'Reason' => $reason
    ];

    // Enviar dados para o Service Layer do SAP Business One
    $url = $serviceLayerUrl . "UserTablesUDO('TROCAS')";
    $headers = [
        "Content-Type: application/json",
        "Cookie: B1SESSION=$sessionId"
    ];

    $options = [
        'http' => [
            'header' => implode("\r\n", $headers),
            'method' => 'PATCH',
            'content' => json_encode($exchangeData)
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

        // Mensagem de sucesso
        echo "Troca registrada com sucesso!";
    } catch (Exception $e) {
        echo "Erro ao registrar troca: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cadastrar Troca de Produto</title>
    <link href="./css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container my-4">
        <h1 class="text-center mb-4">Cadastrar Troca de Produto</h1>

        <!-- Formulário de cadastro de troca -->
        <form method="POST">
            <div class="mb-3">
                <label for="ItemCode" class="form-label">Código do Item:</label>
                <input type="text" class="form-control" name="ItemCode" id="ItemCode" required>
            </div>
            <div class="mb-3">
                <label for="Quantity" class="form-label">Quantidade:</label>
                <input type="number" class="form-control" name="Quantity" id="Quantity" required>
            </div>
            <div class="mb-3">
                <label for="ExchangeDate" class="form-label">Data da Troca:</label>
                <input type="date" class="form-control" name="ExchangeDate" id="ExchangeDate" required>
            </div>
            <div class="mb-3">
                <label for="Reason" class="form-label">Motivo da Troca:</label>
                <textarea class="form-control" name="Reason" id="Reason" rows="3" required></textarea>
            </div>
            <button type="submit" class="btn btn-primary">Registrar Troca</button>
        </form>

        <a href="index.php" class="btn btn-primary mt-4">Início</a>
        <a href="logout.php" class="btn btn-danger mt-4">Sair</a>
    </div>
</body>
</html>
