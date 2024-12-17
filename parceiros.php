<?php
session_start();
if (empty($_SESSION['B1SESSION'])) {
    header('Location: login.php');
    exit;
}

$sessionId = $_SESSION['B1SESSION'];
$serviceLayerUrl = "https://192.168.0.10:50000/b1s/v1/";
$pageSize = 20; // Número de resultados por página
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$filters = [
    'CardCode' => $_GET['CardCode'] ?? '',
    'CardName' => $_GET['CardName'] ?? ''
];

function getBusinessPartners($serviceLayerUrl, $sessionId, $filters, $page, $pageSize) {
    $skip = ($page - 1) * $pageSize;

    // Construir a query OData para filtros
    $filterString = "";
    if (!empty($filters['CardCode'])) {
        $filterString .= "startswith(CardCode,'{$filters['CardCode']}')";
    }
    if (!empty($filters['CardName'])) {
        if (!empty($filterString)) $filterString .= " and ";
        $filterString .= "startswith(CardName,'{$filters['CardName']}')";
    }

    $filterParam = $filterString ? "&\$filter=$filterString" : "";

    // URL com filtros, paginação e ordenação
    $url = $serviceLayerUrl . "BusinessPartners?\$orderby=CardCode&\$top=$pageSize&\$skip=$skip$filterParam";

    // Configurando cabeçalhos e contexto para a requisição
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
        return [];
    }
}

// Buscando dados dos Parceiros de Negócios
$businessPartners = getBusinessPartners($serviceLayerUrl, $sessionId, $filters, $page, $pageSize);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parceiros de Negócios</title>
    <link href="./css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container my-4">
        <h1 class="text-center mb-4">Parceiros de Negócios</h1>

        <!-- Formulário de filtros -->
        <form class="row g-3 mb-4" method="GET">
            <div class="col-md-6">
                <label for="CardCode" class="form-label">Código do Parceiro:</label>
                <input type="text" class="form-control" name="CardCode" id="CardCode" value="<?= htmlspecialchars($filters['CardCode']) ?>">
            </div>
            <div class="col-md-6">
                <label for="CardName" class="form-label">Nome do Parceiro:</label>
                <input type="text" class="form-control" name="CardName" id="CardName" value="<?= htmlspecialchars($filters['CardName']) ?>">
            </div>
            <div class="col-12 text-center">
                <button type="submit" class="btn btn-primary">Buscar</button>
            </div>
        </form>

        <!-- Resultados -->
        <div class="table-responsive">
            <table class="table table-striped">
                <thead class="table-dark">
                <tr>
                    <th>Código do Parceiro</th>
                    <th>Nome do Parceiro</th>
                    <th>Ações</th>
                </tr>
                </thead>
                <tbody>
                    <?php if (!empty($businessPartners['value'])): ?>
                        <?php foreach ($businessPartners['value'] as $partner): ?>
                            <tr>
                                <td><?= htmlspecialchars($partner['CardCode']) ?></td>
                                <td><?= htmlspecialchars($partner['CardName']) ?></td>
                                <td>
                                    <a href="view_partner.php?CardCode=<?= urlencode($partner['CardCode']) ?>" class="btn btn-info btn-sm">Visualizar</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="2" class="text-center">Nenhum parceiro encontrado.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginação -->
        <nav class="d-flex justify-content-center mt-4">
            <ul class="pagination">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">Anterior</a>
                    </li>
                <?php endif; ?>
                <?php if (!empty($businessPartners['value']) && count($businessPartners['value']) === $pageSize): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Próxima</a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>

        <a href="index.php" class="btn btn-primary mt-4">Inicio</a>
        <a href="logout.php" class="btn btn-danger mt-4">Sair</a>
    </div>
</body>
</html>
