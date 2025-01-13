<?php
session_start();
if (empty($_SESSION['B1SESSION'])) {
    header('Location: login.php');
    exit;
}

$sessionId = $_SESSION['B1SESSION'];
$serviceLayerUrl = "https://192.168.0.10:50000/b1s/v1/U_TROCAS";

$cardCode = isset($_GET['CardCode']) ? $_GET['CardCode'] : '';
$cardName = isset($_GET['CardName']) ? $_GET['CardName'] : '';
$currentDate = date('Y-m-d');
// Função para registrar troca no Service Layer
function registerExchange($serviceLayerUrl, $sessionId, $data) {
    $url = $serviceLayerUrl;

    $headers = [
        "Content-Type: application/json",
        "Cookie: B1SESSION=$sessionId"
    ];

    $options = [
        'http' => [
            'header' => implode("\r\n", $headers),
            'method' => 'POST',
            'content' => json_encode($data)
        ],
        'ssl' => [
            'verify_peer' => false,
            'verify_peer_name' => false
        ]
    ];

    $context = stream_context_create($options);

    try {
        $result = file_get_contents($url, false, $context);
        $responseHeaders = $http_response_header;
        $httpStatus = explode(' ', $responseHeaders[0])[1];

        if ($result === false || $httpStatus >= 400) {
            $error = error_get_last();
            throw new Exception("Erro na comunicação com o Service Layer: HTTP $httpStatus. " . $error['message']);
        }

        $response = json_decode($result, true);
        if (isset($response['error'])) {
            throw new Exception("Erro do Service Layer: " . $response['error']['message']['value']);
        }

        return $response;
    } catch (Exception $e) {
        error_log($e->getMessage());
        return ['error' => $e->getMessage()];
    }
}


$message = "";
if ($_SERVER['REQUEST_METHOD'] === 'POST')
    {
        $date = $_POST['U_Date'];
            if (!empty($date)) 
            {
                $dataFormatada = date('Y-m-d', strtotime($date));
            } else {
            $dataFormatada;
            }
        
        $data = [
            "Name" => $_POST['Name'] ?? '',
            "U_CardCode" => $_POST['U_CardCode'] ?? '',
            "U_CardName" => $_POST['U_CardName'] ?? '',
            "U_SWW" => $_POST['sww'] ?? '',
            "U_ItemCode" => $_POST['U_ItemCode'] ?? '',
            "U_Description" => $_POST['U_Description'] ?? '',
            "U_Quantity" => $_POST['U_Quantity'] ?? '',
            "U_Date" => $dataFormatada,
            "U_Reason" => $_POST['U_Reason'] ?? ''
                ];

            $response = registerExchange($serviceLayerUrl, $sessionId, $data);

        if (isset($response['error'])) {
            $message = "<p style='color: red;'>Erro: " . htmlspecialchars($response['error']) . "</p>";
        } else {
            $message = "<p style='color: green;'>Troca registrada com sucesso!</p>";
        }
    }

?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Trocas</title>
    <link href="./css/bootstrap.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <style>
        body    {
            background-color:rgba(104, 213, 235, 0.6);
        }
    </style>
    <script>
    // Função para buscar o Nome do Cliente com base no CardCode
    document.addEventListener("DOMContentLoaded", function() {
        document.getElementById("U_CardCode").addEventListener("change", function() {
            const cardCode = this.value.trim();
            const cardNameField = document.getElementById("U_CardName");

            if (cardCode) {
                fetch(`get_cardname.php?CardCode=${encodeURIComponent(cardCode)}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error("Erro ao buscar o Nome do Cliente.");
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.CardName) {
                            cardNameField.value = data.CardName;
                        } else {
                            alert("Cliente não encontrado.");
                            cardNameField.value = "";
                        }
                    })
                    .catch(error => {
                        alert(error.message);
                        cardNameField.value = "";
                    });
            } else {
                cardNameField.value = "";
            }
        });
    });
    </script>

<script>
    // Função para buscar o Nome do Item com base no ItemCode
    document.addEventListener("DOMContentLoaded", function() {
        document.getElementById("U_ItemCode").addEventListener("change", function() {
            const itemCode = this.value.trim();
            const itemNameField = document.getElementById("U_Description");

            if (itemCode) {
                fetch(`get_item_description.php?ItemCode=${encodeURIComponent(itemCode)}`)
                    .then(response => {
                        if (!response.ok) {
                            throw new Error("Erro ao buscar o Nome do Item.");
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.ItemName) {
                            itemNameField.value = data.ItemName;
                        } else {
                            alert("Item não encontrado.");
                            itemNameField.value = "";
                        }
                    })
                    .catch(error => {
                        alert(error.message);
                        itemNameField.value = "";
                    });
            } else {
                itemNameField.value = "";
            }
        });
    });
    </script>


</head>
<body>
    <div class="container my-4">
        <h1 class="text-center mb-4">Registro de Trocas</h1>
        <?= $message ?>

        <form class="row g-3" method="POST">
            <div class="col-md-2">
                <label for="Name" class="form-label">Numero do Pedido:</label>
                <input type="text" class="form-control" id="Name" name="Name" required>
            </div>
            <div class="col-md-4">
                <label for="U_CardCode" class="form-label">Código do Cliente:</label>
                <input type="text" class="form-control" id="U_CardCode" name="U_CardCode" value="<?php echo htmlspecialchars($cardCode); ?>" required>
            </div>
            <div class="col-md-6">
                <label for="U_CardName" class="form-label">Nome do Cliente:</label>
                <input type="text" class="form-control" id="U_CardName" name="U_CardName" value="<?php echo htmlspecialchars($cardName); ?>" required>
            </div>
            <div class="col-md-2">
                <label for="sww" class="form-label">Referencia do Item:</label>
                <input type="text" class="form-control" id="sww" name="sww" onblur="fetchItemCode()"  placeholder="Digite o Código" >
            </div>
            <div class="col-md-2">
                <label for="U_ItemCode" class="form-label">Código do Item:</label>
                <input type="text" class="form-control" id="U_ItemCode" name="U_ItemCode"  required>
            </div>
            <div class="col-md-8">
                <label for="U_Description" class="form-label">Descrição do Item:</label>
                <input type="text" class="form-control" id="U_Description" name="U_Description" required>
            </div>
            <div class="col-md-2">
                <label for="U_Quantity" class="form-label">Quantidade do Item:</label>
                <input type= "number" class="form-control" id="U_Quantity" name="U_Quantity" required>
            </div>
            <div class="col-md-2">
                <label for="U_Date" class="form-label">Data da Troca:</label>
                <input type="date" class="form-control" id="U_Date" name="U_Date" value="<?= htmlspecialchars($currentDate) ?>" required>
            </div>
            <div class="col-md-8">
                <label for="U_Reason" class="form-label">Motivo da Troca:</label>
                <textarea class="form-control" id="U_Reason" name="U_Reason" rows="3" required></textarea>
            </div>
            <div class="col-12 text-center">
                <button type="submit" class="btn btn-primary">Registrar Troca</button>
            </div>
        </form>

        <a href="index.php" class="btn btn-primary mt-4">Inicio</a>
        <a href="logout.php" class="btn btn-danger mt-4">Sair</a>
    </div>
    <b>
        <p align="center">Desenvolvido por Alumínio Ramos</p>
    </b>
    <script>
     function fetchItemCode() {
            const sww = document.getElementById("sww").value;

            if (!sww) {
                alert("Por favor, insira o SWW do item.");
                return;
            }

            // Fazer a chamada ao fetch_item.php
            fetch("fetch_item.php", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({ sww: sww }),
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error("Erro ao buscar o item.");
                }
                return response.json();
            })
            .then(data => {
                if (data.error) {
                    alert(data.error);
                } else {
                    document.getElementById("U_ItemCode").value = data.ItemCode || "";
                    document.getElementById("U_Description").value = data.ItemName || "";
                }
            })
            .catch(error => {
                console.error("Erro:", error);
                alert("Erro ao buscar as informações do item.");
            });
        };
    </script>
</body>
</html>
