<?php

function isAPIKeyValid()
{
    if(php_sapi_name() === 'cli'){
        return true;
    }
    if(empty($_REQUEST['APISecret'])){
        return false;
    }
    global $global;
    $url = "{$global['webSiteRootURL']}plugin/API/get.json.php?APIName=isAPISecretValid&APISecret={$_REQUEST['APISecret']}";
    $content = file_get_contents($url);
    $json = json_decode($content);
    //var_dump(empty($json->error));
    return !empty($json) && empty($json->error);
}

// Function to load standalone configuration
function loadStandaloneConfiguration()
{
    global $global, $doNotIncludeConfig, $streamerURL;
    global $mysqlHost, $mysqlUser, $mysqlPass, $mysqlDatabase, $mysqlPort, $mysql_connect_was_closed, $mysql_connect_is_persistent;

    $configFile = __DIR__ . "/../videos/configuration.php";
    $global['systemRootPath'] = realpath(__DIR__ . '/../') . '/';
    $global['systemRootPath'] = str_replace('\\', '/', $global['systemRootPath']);
    $configFileStandAlone = "{$global['systemRootPath']}videos/standalone.configuration.php";

    if (file_exists($configFile)) {
        require_once $configFile;
        $streamerURL = $global['webSiteRootURL'];
        error_log("Rebroadcaster.json.php is using local configuration");
        if($isStandAlone){
            if (!isAPIKeyValid()) {
                die('Invalid API Key');
            }
        }
        return true;
    }

    if (file_exists($configFileStandAlone)) {
        $doNotIncludeConfig = 1;
        require_once $configFileStandAlone;
        $configFile = "{$global['systemRootPath']}videos/configuration.php";

        // Check if configuration.php exists; if not, create it
        if (!file_exists($configFile)) {
            $content = "<?php" . PHP_EOL;
            $content .= "global \$global, \$doNotIncludeConfig, \$doNotConnectDatabaseIncludeConfig, \$doNotStartSessionIncludeConfig, \$isStandAlone;" . PHP_EOL;
            $content .= "\$isStandAlone = 1;" . PHP_EOL;
            $content .= "\$doNotIncludeConfig = 1;" . PHP_EOL;
            $content .= "\$doNotConnectDatabaseIncludeConfig = 1;" . PHP_EOL;
            $content .= "\$doNotStartSessionIncludeConfig = 1;" . PHP_EOL;
            $content .= "\$global['salt'] = '" . uniqid() . "';" . PHP_EOL;
            $content .= "\$global['webSiteRootURL'] = '{$global['webSiteRootURL']}';" . PHP_EOL;
            $content .= "\$global['systemRootPath'] = '{$global['systemRootPath']}';" . PHP_EOL;
            $content .= "\$global['systemRootPath'] = '{$global['systemRootPath']}';" . PHP_EOL;
            $content .= "require_once \$global['systemRootPath'] . 'objects/include_config.php';" . PHP_EOL;

            if (file_put_contents($configFile, $content) === false) {
                die("Failed to create the configuration file at $configFile");
            }
            error_log("configuration.php created at $configFile");
        }

        require_once $configFile;
    }
    
    // If no configuration file exists, output a message
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
