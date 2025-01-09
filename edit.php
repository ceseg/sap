<?php
session_start();
if (empty($_SESSION['B1SESSION'])) {
    header('Location: login.php');
    exit;
}

// Configurações iniciais
$sessionId = $_SESSION['B1SESSION'];
$serviceLayerUrl = "https://192.168.0.10:50000/b1s/v1/";

// Verifica se o código da troca foi passado
if (!isset($_GET['Code'])) {
    header('Location: index.php');
    exit;
}

$trocaCode = $_GET['Code'];

// Função para obter os detalhes de uma troca específica
function getTroca($serviceLayerUrl, $sessionId, $trocaCode) {
    $url = $serviceLayerUrl ."U_TROCAS?\$filter=Code eq '$trocaCode'";
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
    if ($result === false) {
        error_log("Erro na requisição ao Service Layer.");
        return null;
    }
    $response = json_decode($result, true);
    if (isset($response['error'])) {
        error_log("Erro no Service Layer: " . json_encode($response['error']));
        return null;
    }
    return $response;
}

// Função para atualizar os detalhes de uma troca
function updateTroca($serviceLayerUrl, $sessionId, $trocaCode, $data) {
    $url = $serviceLayerUrl ."U_TROCAS?\$filter=Code eq '$trocaCode'";
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
    if ($result === false) {
        error_log("Erro na requisição ao Service Layer.");
        return false;
    }
    $response = json_decode($result, true);
    if (isset($response['error'])) {
        error_log("Erro no Service Layer: " . json_encode($response['error']));
        return false;
    }
    return true;
}

// Obtém os detalhes da troca
$troca = getTroca($serviceLayerUrl, $sessionId, $trocaCode);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'U_CardCode' => $_POST['cliente'],
        'U_CardName' => $_POST['nome_cliente'],
        'U_SWW' => $_POST['sww'],
        'U_Description' => $_POST['descricao'],
        'U_Reason' => $_POST['motivo'],
        'U_Date' => date('Y-m-d', strtotime($_POST['data']))
    ];

    if (updateTroca($serviceLayerUrl, $sessionId, $trocaCode, $data)) {
        header('Location: index.php');
        exit;
    } else {
        $error = "Erro ao atualizar a troca.";
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Troca</title>
    <link href="./css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container my-5">
        <h1 class="text-center mb-4">Editar Troca</h1>
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <?php if ($troca): ?>
            <form method="POST">
                <div class="mb-3">
                    <label for="cliente" class="form-label">Código Cliente</label>
                    <input type="text" class="form-control" id="cliente" name="cliente" value="<?= htmlspecialchars($troca['U_CardCode']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="nome_cliente" class="form-label">Nome Cliente</label>
                    <input type="text" class="form-control" id="nome_cliente" name="nome_cliente" value="<?= htmlspecialchars($troca['U_CardName']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="sww" class="form-label">SWW</label>
                    <input type="text" class="form-control" id="sww" name="sww" value="<?= htmlspecialchars($troca['U_SWW']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="descricao" class="form-label">Descrição</label>
                    <input type="text" class="form-control" id="descricao" name="descricao" value="<?= htmlspecialchars($troca['U_Description']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="motivo" class="form-label">Motivo</label>
                    <input type="text" class="form-control" id="motivo" name="motivo" value="<?= htmlspecialchars($troca['U_Reason']) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="data" class="form-label">Data</label>
                    <input type="date" class="form-control" id="data" name="data" value="<?= htmlspecialchars($troca['U_Date']) ?>" required>
                </div>
                <button type="submit" class="btn btn-primary">Salvar</button>
                <a href="index.php" class="btn btn-secondary">Cancelar</a>
            </form>
        <?php else: ?>
            <div class="alert alert-danger">Erro ao carregar os detalhes da troca.</div>
        <?php endif; ?>
    </div>
    <script src="./js/bootstrap.bundle.min.js"></script>
</body>
</html>
