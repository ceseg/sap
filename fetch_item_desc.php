<?php
// Configuração do banco de dados
$host = 'localhost'; // Endereço do servidor
$user = 'root';      // Usuário do banco
$password = '';      // Senha do banco
$dbname = 'Produtos'; // Nome do banco

// Conexão com o banco de dados
$conn = new mysqli($host, $user, $password, $dbname);

// Verifica a conexão
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'message' => 'Erro ao conectar ao banco de dados']));
}

// Obtém o SWW da requisição
$sww = $_POST['sww'] ?? '';

// Validação do SWW
if (empty($sww)) {
    echo json_encode(['success' => false, 'message' => 'SWW não pode estar vazio.']);
    exit;
}

// Consulta ao banco de dados
$sql = "SELECT Descricao FROM produtos WHERE SWW = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('s', $sww);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo json_encode(['success' => true, 'descricao' => $row['Descricao']]);
} else {
    echo json_encode(['success' => false, 'message' => 'SWW não encontrado no banco de dados.']);
}

// Fecha a conexão
$stmt->close();
$conn->close();
?>
