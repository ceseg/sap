<?php
session_start();
if (empty($_SESSION['B1SESSION'])) {
    header('Location: login.php');
    exit;
}

// Configurações iniciais
$sessionId = $_SESSION['B1SESSION'];
$serviceLayerUrl = "https://192.168.0.10:50000/b1s/v1/";

// Obter o código da troca a ser editada
$trocaCode = isset($_GET['Code']) ? $_GET['Code'] : '';

//if (empty($trocaCode)) {
 //   header('Location: index.php');
 //   exit;
//}

// Função para obter os detalhes da troca
function getTroca($serviceLayerUrl, $sessionId, $trocaCode) {
    $url = $serviceLayerUrl . "U_TROCAS?\$filter=Code eq ('$trocaCode')";

    // Configuração de headers
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

    // Requisição ao Service Layer
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

// Função para atualizar os detalhes da troca
function updateTroca($serviceLayerUrl, $sessionId, $trocaCode, $data) {
    $url = $serviceLayerUrl . "U_TROCAS?\$filter=Code eq ('$trocaCode')";

    // Configuração de headers
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

    // Requisição ao Service Layer
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

// Processar o formulário de edição
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

// Obter os detalhes da troca
$troca = getTroca($serviceLayerUrl, $sessionId, $trocaCode);
if (!$troca) {
    header('Location: index.php');
    exit;
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

        <form method="POST" class="row g-3">
            <div class="col-md-6">
                <label for="cliente" class="form-label">Código Cliente</label>
                <input type="text" class="form-control" id="cliente" name="cliente" value="<?= htmlspecialchars($troca['U_CardCode']) ?>" required>
            </div>
            <div class="col-md-6">
                <label for="nome_cliente" class="form-label">Nome Cliente</label>
                <input type="text" class="form-control" id="nome_cliente" name="nome_cliente" value="<?= htmlspecialchars($troca['U_CardName']) ?>" required>
            </div>
            <div class="col-md-6">
                <label for="sww" class="form-label">SWW</label>
                <input type="text" class="form-control" id="sww" name="sww" value="<?= htmlspecialchars($troca['U_SWW']) ?>" required>
            </div>
            <div class="col-md-6">
                <label for="descricao" class="form-label">Descrição</label>
                <input type="text" class="form-control" id="descricao" name="descricao" value="<?= htmlspecialchars($troca['U_Description']) ?>" required>
            </div>
            <div class="col-md-6">
                <label for="motivo" class="form-label">Motivo</label>
                <input type="text" class="form-control" id="motivo" name="motivo" value="<?= htmlspecialchars($troca['U_Reason']) ?>" required>
            </div>
            <div class="col-md-6">
                <label for="data" class="form-label">Data</label>
                <input type="date" class="form-control" id="data" name="data" value="<?= htmlspecialchars($troca['U_Date']) ?>" required>
            </div>
            <div class="col-12">
                <button type="submit" class="btn btn-primary">Salvar</button>
                <a href="index.php" class="btn btn-secondary">Cancelar</a>
            </div>
        </form>
    </div>

    <script src="./js/bootstrap.bundle.min.js"></script>
</body>
</html>
