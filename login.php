<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];
    $companyDB = $_POST['companyDB'];

    $serviceLayerUrl = "https://192.168.0.10:50000/b1s/v1/";

    function authenticate($serviceLayerUrl, $username, $password, $companyDB)
    {
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
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)),
                url('./img/background.jpg') no-repeat center center fixed;
            background-size: cover;
            color: white;
            font-family: 'Arial', sans-serif;
        }

        .login-container {
            max-width: 400px;
            margin: 5% auto;
            background: rgba(0, 0, 0, 0.8);
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.3);
        }

        h1 {
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7);
        }

        footer {
            margin-top: 50px;
            background-color: rgba(0, 0, 0, 0.8);
            color: white;
            padding: 10px 0;
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
        }

        .form-label,
        .form-select,
        .form-control {
            background: transparent;
            color: white;
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #007bff;
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
        }

        /* Alterações para o select */
        .form-select {
            background-color: rgba(0, 123, 255, 0.2);
            /* Fundo levemente azul */
            color: white;
            /* Cor do texto branco */
            border: 1px solid #007bff;
            /* Borda azul */
        }

        .form-select:focus {
            background-color: rgba(0, 123, 255, 0.5);
            /* Fundo azul mais escuro ao focar */
            color: white;
            /* Mantém o texto branco */
            border-color: #0056b3;
            /* Borda azul mais escura ao focar */
            box-shadow: 0 0 5px rgba(0, 123, 255, 0.5);
            /* Efeito de luz ao redor */
        }

        .form-select option {
            background-color: #333;
            /* Cor de fundo das opções */
            color: white;
            /* Cor do texto nas opções */
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
            transition: background-color 0.3s ease;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .alert {
            text-align: center;
            font-weight: bold;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="login-container">
            <h1>Login SAP</h1>
            <form method="POST">
                <div class="mb-3">
                    <label for="username" class="form-label">Usuário:</label>
                    <input type="text" class="form-control" name="username" id="username" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Senha:</label>
                    <input type="password" class="form-control" name="password" id="password" required>
                </div>
                <div class="mb-3">
                    <label for="companyDB" class="form-label">Empresa:</label>
                    <select class="form-select" name="companyDB" id="companyDB" required>
                        <option selected disabled>Escolher...</option>
                        <option value="SBOPRODRM">ALUMÍNIO RAMOS</option>
                        <option value="SBOPRODGE">GERENCIAL</option>
                        <option value="SBOPRODHE">HELTON RAMOS</option>
                        <option value="SBOSIMU_010125">SIMULAÇÃO</option>
                    </select>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Entrar</button>
                </div>
            </form>

            <?php if (!empty($error)): ?>
                <div class="alert alert-danger mt-3"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
        </div>
    </div>
    <footer>
        &copy; 2024 Alumínio Ramos. Todos os direitos reservados.
    </footer>
    <script src="./js/bootstrap.bundle.min.js"></script>
</body>

</html>