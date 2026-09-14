<?php

if (PHP_SAPI !== 'cli') { http_response_code(403); exit('Command Line only'); }
$isANewInstall = !file_exists(__DIR__ . '/../videos/configuration.php');
$encoderScript = __DIR__ . '/../Encoder/install/checkConfiguration.php';
$needsEncoder = is_file($encoderScript) && !file_exists(__DIR__ . '/../Encoder/videos/configuration.php');
if (!$isANewInstall && !$needsEncoder) { exit(0); }

// Resolve admin password — never use the insecure default.
$_resolvedAdminPass = getenv("SYSTEM_ADMIN_PASSWORD");
if (empty($_resolvedAdminPass) || $_resolvedAdminPass === 'password') {
    if (!$isANewInstall) { fwrite(STDERR, "Set SYSTEM_ADMIN_PASSWORD to connect the new Encoder to the existing Streamer.\n"); exit(1); }
    $_resolvedAdminPass = bin2hex(random_bytes(16));
    $videosDirectory = __DIR__ . '/../videos';
    if (!is_dir($videosDirectory) && !mkdir($videosDirectory, 0755, true)) { exit(1); }
    $passwordFile = $videosDirectory . '/.initial_admin_password.php';
    $passwordSource = "<?php return " . var_export($_resolvedAdminPass, true) . ";\n";
    if (file_put_contents($passwordFile, $passwordSource, LOCK_EX) !== strlen($passwordSource) || (PHP_OS_FAMILY !== 'Windows' && !chmod($passwordFile, 0600))) {
        fwrite(STDERR, "Unable to save the initial administrator password. Set SYSTEM_ADMIN_PASSWORD and retry.\n"); exit(1);
    }
    error_log("==================================================");
    error_log("AVIDEO: SYSTEM_ADMIN_PASSWORD was not set or was");
    error_log("        left at the insecure default 'password'.");
    error_log("        A random password has been generated.");
    error_log("Saved to: " . $passwordFile);
    error_log("==================================================");
}

$_POST["systemRootPath"] = str_replace('\\', '/', dirname(__DIR__)) . '/';
$_POST["databaseHost"] = getenv("DB_MYSQL_HOST");
$_POST["databasePort"] = (getenv("DB_MYSQL_PORT") ?: "3306");
$_POST["databaseName"] = getenv("DB_MYSQL_NAME");
$_POST["databaseUser"] = getenv("DB_MYSQL_USER");
$_POST["databasePass"] = (string) getenv("DB_MYSQL_PASSWORD");
$_POST["createTables"] = 1;
$_POST["contactEmail"] = getenv("CONTACT_EMAIL");
$_POST["systemAdminPass"] = $_resolvedAdminPass;
$_POST["webSiteTitle"] = getenv("WEBSITE_TITLE");
$_POST["mainLanguage"] = getenv("MAIN_LANGUAGE");
$_POST["webSiteRootURL"] = "https://".getenv("SERVER_NAME")."/";

if($isANewInstall){
    require_once __DIR__ . '/checkConfiguration.php';
    if (empty($installerSucceeded)) { exit(1); }
    $argv[1] = 1;
    require_once __DIR__ . '/installPluginsTables.php';
}

$_POST['systemRootPath'] = "{$_POST["systemRootPath"]}Encoder/";
$_POST["databaseHost"] = getenv("ENCODER_DB_MYSQL_HOST") ?: "{$_POST["databaseHost"]}_encoder";
$_POST["databasePort"] = getenv("ENCODER_DB_MYSQL_PORT") ?: $_POST["databasePort"];
$_POST["databaseName"] = "{$_POST["databaseName"]}_encoder";
$_POST['tablesPrefix'] = "";
$_POST['createTables'] = 1;
$_POST['systemAdminPass'] = $_resolvedAdminPass;
$_POST['inputUser'] = 'admin';
$_POST['inputPassword'] = $_POST['systemAdminPass'];
$_POST['webSiteTitle'] = "AVideo";
$_POST['siteURL'] = $_POST["webSiteRootURL"];
$_POST['webSiteRootURL'] = $_POST["webSiteRootURL"] . "Encoder/";
$_POST['allowedStreamers'] = $_POST['siteURL'];
$_POST['defaultPriority'] = 6;
$encoderScript = __DIR__ . '/../Encoder/install/checkConfiguration.php';
if (is_file($encoderScript) && !file_exists(__DIR__ . '/../Encoder/videos/configuration.php')) {
    // Standalone installers intentionally have their own helpers. Run the Encoder in
    // a separate PHP process and pass credentials through stdin, never command arguments.
    $runner = '$_POST = json_decode(stream_get_contents(STDIN), true); require $argv[1]; exit(!empty($installerSucceeded) ? 0 : 1);';
    $process = proc_open([PHP_BINARY, '-r', $runner, $encoderScript], [0 => ['pipe', 'r'], 1 => STDOUT, 2 => STDERR], $pipes, dirname($encoderScript));
    if (!is_resource($process)) { fwrite(STDERR, "Unable to start Encoder setup.\n"); exit(1); }
    $payload = json_encode($_POST);
    $offset = 0;
    while ($offset < strlen($payload)) {
        $written = fwrite($pipes[0], substr($payload, $offset));
        if ($written === false || $written === 0) { break; }
        $offset += $written;
    }
    fclose($pipes[0]);
    $status = proc_close($process);
    if ($offset !== strlen($payload) || $status !== 0) { exit(1); }
}
