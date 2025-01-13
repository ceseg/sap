<?php
header("Content-Type: application/json");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents("php://input"), true);
    $sww = $input['sww'] ?? null;

    if (!$sww) {
        echo json_encode(["error" => "O campo SWW é obrigatório."]);
        exit;
    }

    // Configurações do Service Layer
    $serviceLayerUrl = "https://192.168.0.10:50000/b1s/v1/";
    $username = "cesar";
    $password = "9344";
    $companyDB = "SBOPRODRM";

    // Função de autenticação
    function authenticate($url, $username, $password, $companyDB) {
        $data = [
            "UserName" => $username,
            "Password" => $password,
            "CompanyDB" => $companyDB,
        ];

        $options = [
            CURLOPT_URL => $url . "Login",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => ["Content-Type: application/json"],
            CURLOPT_SSL_VERIFYHOST => false,
            CURLOPT_SSL_VERIFYPEER => false,
        ];

        $curl = curl_init();
        curl_setopt_array($curl, $options);
        $response = curl_exec($curl);

        if (curl_errno($curl)) {
            die(json_encode(["error" => "Erro ao autenticar: " . curl_error($curl)]));
        }

        $responseData = json_decode($response, true);
        curl_close($curl);

        if (isset($responseData['SessionId'])) {
            return $responseData['SessionId'];
        } else {
            die(json_encode(["error" => "Falha na autenticação: " . $response]));
        }
    }

    // Autenticação no Service Layer
    $sessionId = authenticate($serviceLayerUrl, $username, $password, $companyDB);

    // Buscar o item pelo SWW
    $query = urlencode("SWW eq '$sww'");
    $url = $serviceLayerUrl . "Items?\$filter=" . $query;

    $options = [
        CURLOPT_URL => $url,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "Cookie: B1SESSION=" . $sessionId,
        ],
        CURLOPT_SSL_VERIFYHOST => false,
        CURLOPT_SSL_VERIFYPEER => false,
    ];

    $curl = curl_init();
    curl_setopt_array($curl, $options);
    $response = curl_exec($curl);

    if (curl_errno($curl)) {
        die(json_encode(["error" => "Erro ao buscar item: " . curl_error($curl)]));
    }

    $responseData = json_decode($response, true);
    curl_close($curl);

    // Verifica se o item foi encontrado
    if (isset($responseData['value']) && count($responseData['value']) > 0) {
        $item = $responseData['value'][0]; // Pega o primeiro item encontrado
        echo json_encode([
            "ItemCode" => $item['ItemCode'] ?? "",
            "ItemName" => $item['ItemName'] ?? "",
        ]);
    } else {
        echo json_encode(["error" => "Nenhum item encontrado para o SWW informado."]);
    }
} else {
    echo json_encode(["error" => "Método de requisição inválido."]);
}
