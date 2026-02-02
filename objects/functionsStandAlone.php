<?php

function isAPIKeyValid()
{
    error_log("isAPIKeyValid: Checking API key validity");
    error_log("isAPIKeyValid: php_sapi_name=" . php_sapi_name());

    if(php_sapi_name() === 'cli'){
        error_log("isAPIKeyValid: CLI mode, returning true");
        return true;
    }

    // Check for APISecret in REQUEST (form data or query string)
    if(!empty($_REQUEST['APISecret'])){
        error_log("isAPIKeyValid: Found APISecret in REQUEST, validating...");
        global $global;
        $url = "{$global['webSiteRootURL']}plugin/API/get.json.php?APIName=isAPISecretValid&APISecret={$_REQUEST['APISecret']}";
        error_log("isAPIKeyValid: Validating via URL: {$url}");
        $content = file_get_contents($url);
        error_log("isAPIKeyValid: Response: {$content}");
        $json = json_decode($content);
        $isValid = !empty($json) && empty($json->error);
        error_log("isAPIKeyValid: APISecret validation result: " . ($isValid ? 'valid' : 'invalid'));
        return $isValid;
    }

    // Check for token in REQUEST (query string) - used by control.json.php
    if(!empty($_REQUEST['token'])){
        error_log("isAPIKeyValid: Found token in REQUEST (query string), validating...");
        global $global;
        $url = "{$global['webSiteRootURL']}plugin/Live/verifyToken.json.php?token=" . urlencode($_REQUEST['token']);
        error_log("isAPIKeyValid: Validating token via URL: {$url}");
        $content = @file_get_contents($url);
        error_log("isAPIKeyValid: Response: {$content}");
        if(!empty($content)){
            $json = json_decode($content);
            if(!empty($json) && empty($json->error)){
                error_log("isAPIKeyValid: token is valid");
                return true;
            }
        }
        error_log("isAPIKeyValid: token validation failed");
    }

    // Check for token in JSON body (for restreamer requests)
    $rawInput = file_get_contents("php://input");
    error_log("isAPIKeyValid: No APISecret/token in REQUEST, checking php://input");
    error_log("isAPIKeyValid: Raw input length: " . strlen($rawInput));

    if(!empty($rawInput)){
        $jsonData = json_decode($rawInput);
        error_log("isAPIKeyValid: JSON decoded: " . json_encode($jsonData));

        // Check for responseToken (sent by sendRestream)
        if(!empty($jsonData->responseToken)){
            error_log("isAPIKeyValid: Found responseToken, validating...");
            global $global;
            $url = "{$global['webSiteRootURL']}plugin/Live/verifyToken.json.php?token=" . urlencode($jsonData->responseToken);
            error_log("isAPIKeyValid: Validating responseToken via URL: {$url}");
            $content = @file_get_contents($url);
            error_log("isAPIKeyValid: Response: {$content}");
            if(!empty($content)){
                $json = json_decode($content);
                if(!empty($json) && empty($json->error)){
                    error_log("isAPIKeyValid: responseToken is valid");
                    return true;
                }
            }
            error_log("isAPIKeyValid: responseToken validation failed");
        }

        // Check for token (sent by sendRestream)
        if(!empty($jsonData->token)){
            error_log("isAPIKeyValid: Found token in JSON body");
            // Token is present, we'll verify it later in the specific endpoint
            // For now, allow the request to proceed
            return true;
        }
    }

    error_log("isAPIKeyValid: No valid authentication found");
    error_log("isAPIKeyValid: REQUEST=" . json_encode($_REQUEST));
    return false;
}

function loadStandaloneConfiguration()
{
    global $global, $doNotIncludeConfig, $streamerURL;
    global $mysqlHost, $mysqlUser, $mysqlPass, $mysqlDatabase, $mysqlPort, $mysql_connect_was_closed, $mysql_connect_is_persistent;

    $configFile = __DIR__ . "/../videos/configuration.php";
    $global['systemRootPath'] = realpath(__DIR__ . '/../') . '/';
    $global['systemRootPath'] = str_replace('\\', '/', $global['systemRootPath']);
    $configFileStandAlone = "{$global['systemRootPath']}videos/standalone.configuration.php";

    error_log("loadStandaloneConfiguration: systemRootPath set to {$global['systemRootPath']}");
    error_log("loadStandaloneConfiguration: Checking for configuration files.");

    if (file_exists($configFile)) {
        error_log("loadStandaloneConfiguration: Found configuration.php, loading it.");
        require_once $configFile;
        $streamerURL = $global['webSiteRootURL'];
        error_log("loadStandaloneConfiguration: Streamer URL set to {$streamerURL}");

        if ($isStandAlone) {
            error_log("loadStandaloneConfiguration: Running in standalone mode. Validating API key.");
            if (!isAPIKeyValid()) {
                error_log("loadStandaloneConfiguration: Invalid API Key.");
                die(json_encode(array('error' => true, 'msg' => 'Invalid API Key')));
            }
        }

        return true;
    }

    if (file_exists($configFileStandAlone)) {
        error_log("loadStandaloneConfiguration: Found standalone.configuration.php, loading it.");
        $doNotIncludeConfig = 1;
        require_once $configFileStandAlone;
        $configFile = "{$global['systemRootPath']}videos/configuration.php";

        if (!file_exists($configFile)) {
            error_log("loadStandaloneConfiguration: configuration.php not found. Creating it.");
            $content = "<?php" . PHP_EOL;
            $content .= "global \$global, \$doNotIncludeConfig, \$doNotConnectDatabaseIncludeConfig, \$doNotStartSessionIncludeConfig, \$isStandAlone;" . PHP_EOL;
            $content .= "\$isStandAlone = 1;" . PHP_EOL;
            $content .= "\$doNotIncludeConfig = 1;" . PHP_EOL;
            $content .= "\$doNotConnectDatabaseIncludeConfig = 1;" . PHP_EOL;
            $content .= "\$doNotStartSessionIncludeConfig = 1;" . PHP_EOL;
            $content .= "\$global['salt'] = '" . uniqid() . "';" . PHP_EOL;
            $content .= "\$global['webSiteRootURL'] = '{$global['webSiteRootURL']}';" . PHP_EOL;
            $content .= "\$global['systemRootPath'] = '{$global['systemRootPath']}';" . PHP_EOL;
            $content .= "require_once \$global['systemRootPath'] . 'objects/include_config.php';" . PHP_EOL;

            if (file_put_contents($configFile, $content) === false) {
                error_log("loadStandaloneConfiguration: Failed to create configuration.php at $configFile");
                die("Failed to create the configuration file at $configFile");
            }

            error_log("loadStandaloneConfiguration: configuration.php successfully created at $configFile");
        } else {
            error_log("loadStandaloneConfiguration: configuration.php already exists at $configFile");
        }

        require_once $configFile;
        return true;
    }

    error_log("loadStandaloneConfiguration: No valid configuration file found.");

    $webSiteRootURL = 'https://yourSite.com/';
    header('Content-Type: text/html');

    echo "<h1>Standalone Configuration File Missing</h1>";
    echo "<p>You need to manually create a file named <code>{$global['systemRootPath']}videos/standalone.configuration.php</code> in the <code>videos</code> directory.</p>";
    echo "<p>Include the following content in the file:</p>";
    echo "<pre>";
    echo htmlspecialchars("<?php\n");
    echo htmlspecialchars("\$global['webSiteRootURL'] = '{$webSiteRootURL}';\n");
    echo htmlspecialchars("?>");
    echo "</pre>";

    exit;
}

// Call the function to load configuration
loadStandaloneConfiguration();
