<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $companyDB = $_POST['companyDB'];

    $serviceLayerUrl = "https://192.168.0.10:50000/b1s/v1/";

    function authenticate($serviceLayerUrl, $username, $password, $companyDB) {
        $url = $serviceLayerUrl . "Login";
        $data = [
            "UserName" => $username,
            "Password" => $password,
            "CompanyDB" => $companyDB
        ];
        $options = [
            'http' => [
                'header' => "Content-Type: application/json",
                'method' => 'POST',
                'content' => json_encode($data)
            ],
            'ssl' => [
                'verify_peer' => false,
                'verify_peer_name' => false
            ]
        ];
        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if ($result === FALSE) {
            return null;
        }

        return json_decode($result, true)['SessionId'];
    }

    $sessionId = authenticate($serviceLayerUrl, $username, $password, $companyDB);

    if ($sessionId) {
        $_SESSION['B1SESSION'] = $sessionId;
        $_SESSION['user_logged_in'] = true;
        header('Location: index.php');
        exit;
    } else {
        $error = "Falha no login. Verifique as credenciais.";
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login SAP</title>
    <link href="./css/bootstrap.min.css" rel="stylesheet">
    <style>
        footer {
            margin-top: 50px;
            background-color: #333;
            color: white;
            padding: 10px 0;
            position: fixed;
            bottom: 0;
            width: 100%;
        }
        body    {
            background-color:rgba(104, 213, 235, 0.6);
        }
    </style> 
</head>
<body>
    <div class="container my-4" >
        <h1 class="text-center mb-4">Login SAP</h1>
        <form method="POST" class="row g-3">
            <div class="md-4">
                <label for="username" class="form-label">Usuário:</label>
                <input type="text" class="form-control" name="username" id="username" required>
            </div>
            <div class="md-4">
                <label for="password" class="form-label">Senha:</label>
                <input type="password" class="form-control" name="password" id="password" required>
            </div>
            <div class="md-4">
                <label for="password" class="form-label">Empresa:</label>
                <select class="form-select" id="autoSizingSelect" name="companyDB" id="companyDB">
                <option selected>Escolher...</option>
                <option value="SBOPRODRM">SBOPRODRM</option>
                <option value="SBOPRODGE">SBOPRODGE</option>
                <option value="SBOPRODHE">SBOPRODHE</option>
                <option value="SBOSIMURM_2604">SBOSIMURM</option>
                </select>
            </div>
            <div class="col-12 text-center">
                <button type="submit" class="btn btn-primary">Entrar</button>
            </div>
        </form>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger mt-4"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
    </div>
    <footer>
    &copy; 2024 Alumínio Ramos. Todos os direitos reservados.
</footer>
</body>
</html>
