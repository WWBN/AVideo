<?php

use function Safe\ob_end_flush;

require_once dirname(__FILE__) . '/../../videos/configuration.php';
require_once $global['systemRootPath'] . 'plugin/YPTSocket/functions.php';

ob_end_flush();
$stdin = fopen("php://stdin", "r");

// Log that PHP Worker has started
echo json_encode(["error" => false, "msg" => "PHP Worker started", "response" => null]) . PHP_EOL;

while (true) {
    $input = fgets($stdin);
    if (!$input) continue;

    $input = trim($input);

    if ($input === "exit") {
        echo json_encode(["error" => false, "msg" => "Shutting down", "response" => null]) . PHP_EOL;
        break;
    }


    $data = json_decode($input, true);
    if (!$data || !isset($data["id"], $data["action"])) {
        echo json_encode(["id" => $data["id"], "error" => true, "msg" => "Invalid JSON or missing fields", "response" => null]) . PHP_EOL;
        continue;
    }

    $result = null;
    $error = false;
    $msg = "";

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
                if(empty($global['SocketDataObj'])){
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
        $msg = "Internal Server Error " . $e->getMessage();
    }

    $response = json_encode(["id" => $data["id"], "error" => $error, "msg" => $msg, "response" => $result]) . PHP_EOL;

    echo $response;
    flush();
}

fclose($stdin);
_error_log("🛑 [PHP Worker] Worker has stopped.");

// Helper functions
function getUserFromDatabase($userId)
{
    global $db; // Assume $db is a PDO instance
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$userId]);
    return $stmt->fetch(PDO::FETCH_ASSOC);
}
