<?php
error_reporting(E_ALL);           // Report all types of errors
ini_set('display_errors', '1');

if (empty($_REQUEST['APISecret'])) {
    $_REQUEST['APISecret'] = _getBearerToken();
}

//redirectIfPortOpen(3000);

$requestUri = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

//error_log('APISecret: ' . json_encode($_REQUEST['APISecret']));

// Remove query string
$uri = parse_url($requestUri, PHP_URL_PATH);

/*
 * Check if the request is for the API
 * Example: /api/PluginName/method
 */
$matches = [];
if (preg_match('#^/api/([^/]+)#', $uri, $matches)) {
    $apiName = $matches[1];

    switch ($method) {
        case 'GET':
            include __DIR__ . "/get.json.php";
            break;

        case 'POST':
        case 'DELETE':
            include __DIR__ . "/set.json.php";
            break;

        default:
            http_response_code(405);
            echo json_encode(["error" => "Method Not Allowed"]);
            break;
    }
} else {
    http_response_code(404);
    echo json_encode(["error" => "Not Found"]);
}

/**
 * Redirects the request to a new port if it is open.
 *
 * @param int $newPort The new port to redirect to.
 * @param int $timeout The timeout for the connection check (default: 1 second).
 */
function redirectIfPortOpen($newPort, $timeout = 1)
{
    $host = $_SERVER['SERVER_NAME'];
    $currentPort = $_SERVER['SERVER_PORT'];
    $targetHost = $host;
    $targetPort = (int)$newPort;

    if ($currentPort == $targetPort) {
        error_log("🔄 Current port is already target port ($targetPort), skipping redirect.");
        return;
    }

    $connection = @fsockopen($targetHost, $targetPort, $errno, $errstr, $timeout);
    if (!$connection) {
        error_log("❌ Port $targetPort is not open or connection failed: $errstr ($errno)");
        return;
    }
    fclose($connection);

    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https" : "http";
    $uri = $_SERVER['REQUEST_URI'];
    $url = "{$scheme}://{$targetHost}:{$targetPort}{$uri}";
    error_log("🔁 Redirecting to: $url");

    // Get headers using getallheaders() if available
    $headers = function_exists('getallheaders') ? getallheaders() : [];
    if (empty($headers)) {
        error_log("⚠️ getallheaders() returned empty. Building manually from \$_SERVER.");
        foreach ($_SERVER as $key => $value) {
            if (stripos($key, 'HTTP_') === 0) {
                $headerName = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                $headers[$headerName] = $value;
            }
        }
    }

    // Fallback: Check for authorization header explicitly in $_SERVER
    if (empty($headers['Authorization'])) {
        if (!empty($_SERVER['HTTP_AUTHORIZATION'])) {
            $headers['Authorization'] = $_SERVER['HTTP_AUTHORIZATION'];
            error_log("🔐 Fallback: Found HTTP_AUTHORIZATION in \$_SERVER: " . $_SERVER['HTTP_AUTHORIZATION']);
        } elseif (!empty($_SERVER['REDIRECT_HTTP_AUTHORIZATION'])) {
            $headers['Authorization'] = $_SERVER['REDIRECT_HTTP_AUTHORIZATION'];
            error_log("🔐 Fallback: Found REDIRECT_HTTP_AUTHORIZATION in \$_SERVER: " . $_SERVER['REDIRECT_HTTP_AUTHORIZATION']);
        } else {
            // Attempt _getBearerToken() as last resort.
            $bearerToken = _getBearerToken();
            if ($bearerToken) {
                $headers['Authorization'] = "Bearer {$bearerToken}";
                error_log("🔐 Fallback: Retrieved token via _getBearerToken(): Bearer {$bearerToken}");
            }
        }
    }

    error_log("📦 Forwarding headers:");
    foreach ($headers as $key => $value) {
        error_log("➡️ $key: $value");
    }

    $method = $_SERVER['REQUEST_METHOD'];
    $body = file_get_contents('php://input');
    if (!empty($body)) {
        error_log("📝 Request body content: $body");
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $body);

    $curlHeaders = [];
    foreach ($headers as $key => $value) {
        $curlHeaders[] = "$key: $value";
    }
    curl_setopt($ch, CURLOPT_HTTPHEADER, $curlHeaders);

    $response = curl_exec($ch);
    if ($response === false) {
        error_log("❌ cURL error: " . curl_error($ch));
        http_response_code(500);
        echo "Internal cURL error: " . curl_error($ch);
        exit;
    }
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    curl_close($ch);

    error_log("✅ Response received. HTTP Code: $httpCode {$url}");

    $headerContent = substr($response, 0, $headerSize);
    $bodyContent = substr($response, $headerSize);

    foreach (explode("\r\n", $headerContent) as $headerLine) {
        if (stripos($headerLine, 'Transfer-Encoding:') === 0) continue;
        if (stripos($headerLine, 'Content-Length:') === 0) continue;
        if (!empty($headerLine) && stripos($headerLine, 'HTTP/') !== 0) {
            header($headerLine, true);
        }
    }

    http_response_code($httpCode);
    echo $bodyContent;
    exit;
}
/**
 * Retrieves the Bearer token from the Authorization header.
 *
 * This function attempts to retrieve the Bearer token from various sources:
 * 1. Apache request headers (if available).
 * 2. All headers using getallheaders().
 * 3. Manually builds headers from $_SERVER if both previous methods fail.
 * 4. Checks for the Authorization header in $_SERVER directly.
 *
 * @return string|null The Bearer token if found, null otherwise.
 */
function _getBearerToken()
{
    $headers = [];

    // 1. Try apache_request_headers() if available
    if (function_exists('apache_request_headers')) {
        $headers = apache_request_headers();
    }

    // 2. If still empty, try getallheaders()
    if (empty($headers) && function_exists('getallheaders')) {
        $headers = getallheaders();
    }

    // 3. If still empty, manually build headers from $_SERVER
    if (empty($headers)) {
        foreach ($_SERVER as $key => $value) {
            if (stripos($key, 'HTTP_') === 0) {
                // Convert HTTP_HEADER_NAME to Header-Name
                $headerName = str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($key, 5)))));
                $headers[$headerName] = $value;
            }
        }
    }

    // 4. Normalize and extract Authorization header
    foreach ($headers as $name => $value) {
        if (strcasecmp($name, 'Authorization') === 0 && preg_match('/Bearer\s(\S+)/', $value, $matches)) {
            return $matches[1]; // Return the token
        }
    }

    // 5. Final fallback: check $_SERVER directly
    if (isset($_SERVER['HTTP_AUTHORIZATION']) && preg_match('/Bearer\s(\S+)/', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
        return $matches[1];
    }

    if (isset($_SERVER['REDIRECT_HTTP_AUTHORIZATION']) && preg_match('/Bearer\s(\S+)/', $_SERVER['REDIRECT_HTTP_AUTHORIZATION'], $matches)) {
        return $matches[1];
    }

    return null; // Token not found
}
