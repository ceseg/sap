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
$filters = [
    'CardCode' => trim($_GET['CardCode'] ?? ''),
    'CardName' => trim($_GET['CardName'] ?? '')
];

/**
 * Função para obter Parceiros de Negócios usando o Service Layer do SAP.
 */
function getBusinessPartners($serviceLayerUrl, $sessionId, $filters, $page, $pageSize) {
    $skip = ($page - 1) * $pageSize;

    // Montar filtros OData
    $filterClauses = [];
    if (!empty($filters['CardCode'])) {
        $filterClauses[] = "startswith(CardCode,'" . urlencode($filters['CardCode']) . "')";
    }
    if (!empty($filters['CardName'])) {
        $filterClauses[] = "startswith(CardName,'" . urlencode($filters['CardName']) . "')";
    }
    $filterString = $filterClauses ? '&$filter=' . implode(' and ', $filterClauses) : '';

    $url = $serviceLayerUrl . "BusinessPartners?\$orderby=CardCode&\$top=$pageSize&\$skip=$skip$filterString";

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
        error_log("Erro no Service Layer: " . $response['error']['message']['value']);
        return [];
    }

    return $response;
}

// Busca dados
$businessPartners = getBusinessPartners($serviceLayerUrl, $sessionId, $filters, $page, $pageSize);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Parceiros de Negócios</title>
    <link href="./css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(to right, #74ebd5, #acb6e5);
            color: #333;
        }
        footer {
            margin-top: 50px;
            background-color: #333;
            color: #fff;
            padding: 10px 0;
            text-align: center;
        }
        .btn-custom {
            background-color: #007bff;
            color: #fff;
            border: none;
        }
        .btn-custom:hover {
            background-color: #0056b3;
        }
    </style>
</head>
<body>
    <div class="container my-5">
        <h1 class="text-center mb-4">Parceiros de Negócios</h1>

        <!-- Formulário de Filtro -->
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
                <button type="submit" class="btn btn-custom">Buscar</button>
            </div>
        </form>

        <!-- Tabela de Resultados -->
        <div class="table-responsive">
            <table class="table table-hover">
                <thead class="table-dark">
                    <tr>
                        <th>Código</th>
                        <th>Nome</th>
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
                                    <a href="view_partner.php?CardCode=<?= urlencode($partner['CardCode']) ?>" class="btn btn-info btn-sm">Detalhes</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" class="text-center">Nenhum parceiro encontrado.</td>
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
                <?php if (!empty($businessPartners['value']) && count($businessPartners['value']) === $pageSize): ?>
                    <li class="page-item"><a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">Próxima</a></li>
                <?php endif; ?>
            </ul>
        </nav>
        <div class="mt-4 text-center">
            <a href="index.php" class="btn btn-primary">Início</a>
            <a href="logout.php" class="btn btn-danger">Sair</a>
        </div>
    </div>

    <footer>
        Desenvolvido por Alumínio Ramos
    </footer>
</body>
</html>
