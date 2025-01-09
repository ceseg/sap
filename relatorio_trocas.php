<?php
session_start();
if (empty($_SESSION['B1SESSION'])) {
    header('Location: login.php');
    exit;
}

// Configurações iniciais
$sessionId = $_SESSION['B1SESSION'];
$serviceLayerUrl = "https://192.168.0.10:50000/b1s/v1/";
$pageSize = 20;
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;

// Parâmetros de filtro
$filtroPedido = isset($_GET['pedido']) ? $_GET['pedido'] : '';
$filtroCliente = isset($_GET['cliente']) ? $_GET['cliente'] : '';
$filtroData = isset($_GET['data']) ? $_GET['data'] : '';

/**
 * Função para obter trocas cadastradas com filtros.
 */
function getTrocas($serviceLayerUrl, $sessionId, $page, $pageSize, $filtroPedido, $filtroCliente, $filtroData) {
    $skip = ($page - 1) * $pageSize;
    $url = $serviceLayerUrl . "U_TROCAS?\$top=$pageSize&\$skip=$skip";

    // Adicionar filtros na URL
    $filtros = [];
    if (!empty($filtroPedido)) {
        $filtros[] = "Name eq '$filtroPedido'";
    }
    if (!empty($filtroCliente)) {
        $filtros[] = "U_CardCode eq '$filtroCliente'";
    }
    if (!empty($filtroData)) {
        $filtros[] = "U_Date eq '" . date('Y-m-d', strtotime($filtroData)) . "'";
    }
    if (!empty($filtros)) {
        $url .= "&\$filter=" . urlencode(implode(' and ', $filtros));
    }

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
        return [];
    }

    $response = json_decode($result, true);
    if (isset($response['error'])) {
        error_log("Erro no Service Layer: " . json_encode($response['error']));
        return [];
    }

    return $response;
}


// Busca dados
$trocas = getTrocas($serviceLayerUrl, $sessionId, $page, $pageSize, $filtroPedido, $filtroCliente, $filtroData);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Trocas Cadastradas</title>
    <link href= "./css/bootstrap.min.css" rel="stylesheet">
</head>
<body>

    
    <!-- Conteúdo Principal -->
    <div class="container my-5">
        <h1 class="text-center mb-4">Trocas Cadastradas</h1>

        <!-- Filtro -->
        <form method="GET" class="row g-3 mb-4">
            <div class="col-md-3">
                <label for="pedido" class="form-label">Pedido</label>
                <input type="text" class="form-control" id="pedido" name="pedido" value="<?= htmlspecialchars($filtroPedido) ?>">
            </div>
            <div class="col-md-3">
                <label for="cliente" class="form-label">Cliente</label>
                <input type="text" class="form-control" id="cliente" name="cliente" value="<?= htmlspecialchars($filtroCliente) ?>">
            </div>
            <div class="col-md-3">
                <label for="data" class="form-label">Data</label>
                <input type="date" class="form-control" id="data" name="data" value="<?= htmlspecialchars($filtroData) ?>">
            </div>
            <div class="col-md-3 d-flex align-items-end">
                <button type="submit" class="btn btn-primary w-100">Filtrar</button>
            </div>
        </form>

        <!-- Tabela de Resultados -->
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle">
            <thead class="table-dark">
    <tr>
        <th>Código</th>
        <th>Pedido</th>
        <th>Código Cliente</th>
        <th>Nome Cliente</th>
        <th>SWW</th>
        <th>Descrição</th>
        <th>Motivo</th>
        <th>Data</th>
        <th>Ações</th>
    </tr>
</thead>
<tbody>
    <?php if (!empty($trocas['value'])): ?>
        <?php foreach ($trocas['value'] as $troca): ?>
            <tr>
                <td><?= htmlspecialchars($troca['Code']) ?></td>
                <td><?= htmlspecialchars($troca['Name']) ?></td>
                <td><?= htmlspecialchars($troca['U_CardCode']) ?></td>
                <td><?= htmlspecialchars($troca['U_CardName']) ?></td>
                <td><?= htmlspecialchars($troca['U_SWW']) ?></td>
                <td><?= htmlspecialchars($troca['U_Description']) ?></td>
                <td><?= htmlspecialchars($troca['U_Reason']) ?></td>
                <td><?= htmlspecialchars($troca['U_Date']) ?></td>
                <td>
                    <a href="edit.php?Code=<?= urlencode($troca['Code']) ?>" class="btn btn-warning btn-sm">Editar</a>
                    <a href="excluir.php?Code=<?= urlencode($troca['Code']) ?>" class="btn btn-danger btn-sm" onclick="return confirm('Tem certeza que deseja excluir esta troca?')">Excluir</a>
                </td>
            </tr>
        <?php endforeach; ?>
    <?php else: ?>
        <tr>
            <td colspan="8" class="text-center">Nenhuma troca encontrada.</td>
        </tr>
    <?php endif; ?>
</tbody>

            </table>
        </div>

        <!-- Paginação -->
        <nav class="d-flex justify-content-center mt-4">
            <ul class="pagination">
                <?php if ($page > 1): ?>
                    <li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">Anterior</a></li>
                <?php endif; ?>
                <?php if (!empty($trocas['value']) && count($trocas['value']) === $pageSize): ?>
                    <li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Próxima</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <div class="mt-4 text-center">
            <a href="index.php" class="btn btn-primary">Início</a>
            <a href="logout.php" class="btn btn-danger">Sair</a>
        </div>
    </div>

    <script src="./js/bootstrap.bundle.min.js"></script>
</body>
</html>
