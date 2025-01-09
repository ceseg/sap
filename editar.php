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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Atualizar a troca
    $data = [
        'Name' => $_POST['Name'],
        'U_CardCode' => $_POST['U_CardCode'],
        'U_CardName' => $_POST['U_CardName'],
        'U_SWW' => $_POST['U_SWW'],
        'U_Description' => $_POST['U_Description'],
        'U_Reason' => $_POST['U_Reason'],
        'U_Date' => $_POST['U_Date']
    ];

    $url = $serviceLayerUrl . "U_TROCAS('$code')";
    $headers = [
        "Content-Type: application/json",
        "Cookie: B1SESSION=$sessionId"
    ];

    $options = [
        'http' => [
            'header' => implode("\r\n", $headers),
            'method' => 'PATCH',
            'content' => json_encode($data),
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
        echo "Erro ao atualizar troca.";
    }
} else {
    // Buscar dados da troca
    $url = $serviceLayerUrl . "U_TROCAS('$code')";
    $headers = [
        "Content-Type: application/json",
        "Cookie: B1SESSION=$sessionId"
    ];

    $options = [
        'http' => [
            'header' => implode("\r\n", $headers),
            'method' => 'GET',
            'ignore_errors' => true
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false
        ]
    ];

    $context = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    $troca = json_decode($result, true);
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Troca</title>
    <link href= "./css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h2>Editar Troca</h2>
    <form method="post">
        <div class="mb-3">
            <label for="Name" class="form-label">Pedido</label>
            <input type="text" class="form-control" id="Name" name="Name" value="<?= htmlspecialchars($troca['Name']) ?>" readonly>
        </div>
        <div class="mb-3">
            <label for="U_CardCode" class="form-label">Código Cliente</label>
            <input type="text" class="form-control" id="U_CardCode" name="U_CardCode" value="<?= htmlspecialchars($troca['U_CardCode']) ?>">
        </div>
        <div class="mb-3">
            <label for="U_CardName" class="form-label">Nome Cliente</label>
            <input type="text" class="form-control" id="U_CardName" name="U_CardName" value="<?= htmlspecialchars($troca['U_CardName']) ?>">
        </div>
        <div class="mb-3">
            <label for="U_SWW" class="form-label">SWW</label>
            <input type="text" class="form-control" id="U_SWW" name="U_SWW" value="<?= htmlspecialchars($troca['U_SWW']) ?>">
        </div>
        <div class="mb-3">
            <label for="U_Description" class="form-label">Descrição</label>
            <input type="text" class="form-control" id="U_Description" name="U_Description" value="<?= htmlspecialchars($troca['U_Description']) ?>">
        </div>
        <div class="mb-3">
            <label for="U_Reason" class="form-label">Motivo</label>
            <input type="text" class="form-control" id="U_Reason" name="U_Reason" value="<?= htmlspecialchars($troca['U_Reason']) ?>">
        </div>
        <div class="mb-3">
            <label for="U_Date" class="form-label">Data</label>
            <input type="date" class="form-control" id="U_Date" name="U_Date" value="<?= htmlspecialchars($troca['U_Date']) ?>">
        </div>
        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="index.php" class="btn btn-secondary">Cancelar</a>
    </form>
</div>
<script src="./js/bootstrap.bundle.min.js"></script>
</body>
</html>
