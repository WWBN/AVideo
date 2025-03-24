<?php

use function Safe\ob_end_flush;

require_once dirname(__FILE__) . '/../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/YPTSocket/functions.php';

ob_end_flush();

// 🔒 Força modo bloqueante para aguardar dados no STDIN
$stdin = fopen("php://stdin", "r");
stream_set_blocking($stdin, true);

// Log de inicialização
echo json_encode([
    "error" => false,
    "msg" => "PHP Worker started",
    "response" => null
]) . PHP_EOL;

while (true) {
    // Lê a linha do STDIN (bloqueia até que algo chegue)
    $input = fgets($stdin);

    // Se não retornou nada (EOF ou falha momentânea), pausa um pouco e continua
    if ($input === false) {
        usleep(10000); // 0.01s
        continue;
    }

    $input = trim($input);

    // Se receber "exit", finaliza o loop
    if ($input === "exit") {
        echo json_encode([
            "error" => false,
            "msg" => "Shutting down",
            "response" => null
        ]) . PHP_EOL;
        break;
    }

    // Tenta decodificar JSON e validar campos
    $data = json_decode($input, true);
    if (!$data || !isset($data["id"], $data["action"])) {
        echo json_encode([
            "id" => $data["id"] ?? null,
            "error" => true,
            "msg" => "Invalid JSON or missing fields",
            "response" => null
        ]) . PHP_EOL;
        continue;
    }

    $result = null;
    $error = false;
    $msg = "";

    // Tenta executar a ação solicitada
    try {
        switch ($data["action"]) {
            case "deviceIdToObject":
                $result = deviceIdToObject($data["yptDeviceId"]);
                if (!$result) {
                    $error = true;
                    $msg = "User not found";
                }
                break;

            case "SocketDataObj":
                if (empty($global['SocketDataObj'])) {
                    $global['SocketDataObj'] = AVideoPlugin::getDataObject("YPTSocket");
                    $global['SocketDataObj']->serverVersion = YPTSocket::getServerVersion();
                }
                $result = $global['SocketDataObj'];
                if (!$result) {
                    $error = true;
                    $msg = "SocketDataObj not found";
                }
                break;

            case "getNameIdentificationById":
                $userId = intval($data["users_id"]);
                $result = User::getNameIdentificationById($userId);
                if (!$result) {
                    $error = true;
                    $msg = "User not found";
                }
                break;

            case "getDecryptedInfo":
                $result = getDecryptedInfo($data["token"]);
                break;

            default:
                $error = true;
                $msg = "Unknown action";
        }
    } catch (Exception $e) {
        $error = true;
        $msg = "Internal Server Error: " . $e->getMessage();
    }

    // Retorno ao Node.js
    $response = json_encode([
        "id" => $data["id"],
        "error" => $error,
        "msg" => $msg,
        "response" => $result
    ]) . PHP_EOL;

    echo $response;
    flush();
}

// Encerra leitura do STDIN
fclose($stdin);
_error_log("🛑 [PHP Worker] Worker has stopped.");

/**
 * Exemplo de função auxiliar para buscar um usuário no banco
 * (caso seja necessária)
 */
function getUserFromDatabase($userId)
{
    global $db; // Assume que $db é PDO
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
