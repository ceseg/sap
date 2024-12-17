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
</head>
<body>
<nav class="navbar navbar-expand-lg bg-body-tertiary">
  <div class="container-fluid">
    <a class="navbar-brand" href="./index.php">
    <img src="./img/logo.png"  alt="Bootstrap" width="90" height="44">
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
          <a class="nav-link" href="./logout.php">Sair</a>
        </li>
      </ul>
    </div>
  </div>
</nav>
<h1 class="display-1" align="center">Acesso ao SAP</h1>
</body>
</html>
