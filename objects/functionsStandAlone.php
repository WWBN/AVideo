<?php
// Function to load standalone configuration
function loadStandaloneConfiguration()
{
    global $global, $doNotIncludeConfig, $streamerURL;

    // Define the global system root path
    $global['systemRootPath'] = realpath(__DIR__ . '/../') . '/';
    $global['systemRootPath'] = str_replace('\\', '/', $global['systemRootPath']);
    $configFileStandAlone = "{$global['systemRootPath']}videos/standalone.configuration.php";

    // Load configuration if the file exists
    if (file_exists($configFileStandAlone)) {
        $doNotIncludeConfig = 1;
        require_once $configFileStandAlone;
        // Set global variables for logging
        if ($global['webSiteRootURL'] === _getCurrentUrl()) {
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
                $content .= "\$global['logfile'] = \$global['systemRootPath'] . 'videos/avideo.log';" . PHP_EOL . PHP_EOL;
                $content .= "require_once \$global['systemRootPath'] . 'objects/include_config.php';" . PHP_EOL;

                if (file_put_contents($configFile, $content) === false) {
                    die("Failed to create the configuration file at $configFile");
                }
                error_log("configuration.php created at $configFile");
            }

            require_once $configFile;
        }
    }
}


function _getCurrentUrl()
{
    // Determine the protocol (http or https)
    $protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? "https://" : "http://";

    // Get the host
    $host = $_SERVER['HTTP_HOST'];

    // Get the request URI
    $uri = $_SERVER['REQUEST_URI'];

    // Combine all parts to form the full URL
    $url = $protocol . $host . $uri;

    return str_replace('plugin/Live/standAloneFiles/restreamer.json.php', '', $url);
}

// Call the function to load configuration
loadStandaloneConfiguration();
