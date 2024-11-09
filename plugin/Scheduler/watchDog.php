<?php

//streamer config
require_once dirname(__FILE__) . '/../../videos/configuration.php';

if (!isCommandLineInterface() && !User::isAdmin()) {
    return die('Command Line only');
}

if (!$obj = AVideoPlugin::getDataObjectIfEnabled('Scheduler')) {
    return die('Scheduler is disabled');
}

// check socket
if ($obj->watchDogSocket) {
    if ($objParam = AVideoPlugin::getDataObjectIfEnabled('YPTSocket')) {
        if (!is_port_open($objParam->port)) {
            _error_log("WatchDog: socket port is not opened [{$objParam->port}]");
            $global['systemRootPath'] . 'plugin/YPTSocket/functions.php';
            restartServer();
        } else {
            //_error_log("WatchDog: socket port is opened [{$objParam->port}]");
        }
    }
}

if ($objParam = AVideoPlugin::getDataObjectIfEnabled('Live')) {
    $nginxFile = '/usr/local/nginx/sbin/nginx';
    if (file_exists($nginxFile)) {
        // check live
        $port = Live::getPlayerDestinationPort();
        $address = Live::getPlayerDestinationHost();

        if ($obj->watchDogLiveServer) {
            if (!is_port_open($port)) {
                _error_log("WatchDog: Live port is not opened [{$port}]");
                exec("{$nginxFile} -s stop");
                execAsync("{$nginxFile}");
            } else {
                //_error_log("WatchDog: Live port is opened [{$port}]");
            }
        }

        if ($obj->watchDogLiveServerSSL) {
            // check live ssl
            if (!is_ssl_certificate_valid($port, $address)) {
                _error_log("WatchDog: Live SSL is invalid [port=$port, address=$address]");
                exec("{$nginxFile} -s stop");
                execAsync("{$nginxFile}");
            } else {
                //_error_log("WatchDog: Live SSL is valid [port=$port, address=$address]");
            }
        }
    } else {
        //_error_log("WatchDog: nginx file not found {$nginxFile}");
    }
}


function secureAVideoFolder($folderPath = '/var/www/html/AVideo/videos')
{
    // Check if the folder exists
    if (!is_dir($folderPath)) {
        echo "Folder does not exist.\n";
        return false;
    }

    // Define the current version of the .htaccess file
    $htaccessVersion = '5.9';

    // Define the .htaccess content with updated security rules
    $htaccessContent = "# version $htaccessVersion". PHP_EOL;
    $htaccessContent .= "# SQL was required for the clone plugin" . PHP_EOL;
    $htaccessContent .= "# generated in ".date('Y-m-d H:i:s') . PHP_EOL;

    $htaccessContent .= file_get_contents(__DIR__.'/htaccess.sample.txt');
    // Path to .htaccess file
    $htaccessFile = $folderPath . '/.htaccess';

    // Check if .htaccess file exists
    $updateHtaccess = false;
    if (file_exists($htaccessFile)) {
        // Read the current .htaccess file content
        $currentContent = file_get_contents($htaccessFile);
        // Check if the version in the file matches the current version
        $version = "# version $htaccessVersion";
        if (strpos($currentContent, $version) === false) {
            $updateHtaccess = true;
            _error_log(".htaccess version mismatch $version. Updating to version $htaccessVersion.\n");
        } else {
            //_error_log(".htaccess file is already up-to-date.\n");
        }
    } else {
        $updateHtaccess = true;
        _error_log(".htaccess file not found. Creating a new one with version $htaccessVersion.\n");
    }

    // If the .htaccess needs to be updated, write the new content
    if ($updateHtaccess) {
        file_put_contents($htaccessFile, $htaccessContent);
        _error_log("Updated .htaccess to version $htaccessVersion.\n");
    }

    // Ensure Apache can read and write to this folder
    //shell_exec("chown -R www-data:www-data $folderPath");
    //shell_exec("chmod -R 755 $folderPath");

    // Apply necessary permissions recursively to ensure security and performance
    //shell_exec("find $folderPath -type f -exec chmod 644 {} +");
    //shell_exec("find $folderPath -type d -exec chmod 755 {} +");

    //_error_log("Folder and subfolders are now secure.\n");
    return true;
}

// Run the function to secure the AVideo videos folder
secureAVideoFolder();

