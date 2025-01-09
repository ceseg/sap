<?php
session_start(); // Inicia a sessão

// Verifica se o usuário está logado
if (!isset($_SESSION['user_logged_in']) || $_SESSION['user_logged_in'] !== true) {
    // Redireciona para a página de login
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SAP</title>
    <link href="./css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(rgba(0, 0, 0, 0.6), rgba(0, 0, 0, 0.6)), 
                        url('./img/background.jpg') no-repeat center center fixed; /* Transparência com blend */
            background-size: cover; /* Ajusta a imagem para cobrir todo o fundo */
            color: #fff; /* Texto branco para contraste */
            font-family: 'Arial', sans-serif;
        }
        nav {
            background-color: rgba(0, 0, 0, 0.8); /* Fundo semi-transparente no navbar */
        }
        footer {
            margin-top: 50px;
            background-color: rgba(0, 0, 0, 0.8); /* Fundo semi-transparente no footer */
            color: white;
            padding: 10px 0;
            position: fixed;
            bottom: 0;
            width: 100%;
            text-align: center;
        }
        h1 {
            margin-top: 20%;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.7); /* Sombra para destacar o título */
        }
        .navbar-brand img {
            filter: drop-shadow(2px 2px 5px rgba(0, 0, 0, 0.7)); /* Sombra no logo */
        }
        .nav-link {
            color: white !important;
        }
        .nav-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-lg navbar-dark">
    <div class="container-fluid">
        <a class="navbar-brand" href="./index.php">
            <img src="./img/logo.png" alt="Bootstrap" width="90" height="44">
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavDropdown" aria-controls="navbarNavDropdown" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNavDropdown">
            <ul class="navbar-nav">
                <li class="nav-item">
                    <a class="nav-link active" aria-current="page" href="./index.php">Home</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="./parceiros.php">Parceiros de Negócios</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="./itens.php">Itens</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="./trocas.php">Trocas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="./relatorio_trocas.php">Relatório de Trocas</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="./logout.php">Sair</a>
                </li>
            </ul>
        </div>
    </div>
</nav>
<div class="container text-center">
    <h1 class="display-1">Acesso ao SAP</h1>
</div>
<footer>
    &copy; 2024 Alumínio Ramos. Todos os direitos reservados.
</footer>
<script src="./js/bootstrap.bundle.min.js"></script>
</body>
</html>
