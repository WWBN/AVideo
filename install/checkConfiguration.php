<?php
if (file_exists("../videos/configuration.php")) {
    error_log("Can not create configuration again: ".  json_encode($_SERVER));
    exit;
}


$installationVersion = "14.4";

require_once '../objects/functionsSecurity.php';

error_log("Installation: ".__LINE__." ". json_encode($_POST));
header('Content-Type: application/json');

$obj = new stdClass();
$obj->post = $_POST;

if (!file_exists($_POST['systemRootPath'] . "index.php")) {
    $obj->error = "Your system path to application ({$_POST['systemRootPath']}) is wrong";
    echo json_encode($obj);
    exit;
}
error_log("Installation: ".__LINE__);

$mysqli = @new mysqli($_POST['databaseHost'], $_POST['databaseUser'], $_POST['databasePass'], "", $_POST['databasePort']);

/*
 * This is the "official" OO way to do it,
 * BUT $connect_error was broken until PHP 5.2.9 and 5.3.0.
 */
if ($mysqli->connect_error) {
    $obj->error = ('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
    echo json_encode($obj);
    exit;
}
error_log("Installation: ".__LINE__);

if ($_POST['createTables'] == 2) {
    $sql = "CREATE DATABASE IF NOT EXISTS {$_POST['databaseName']}";
    try {
        $mysqli->query($sql);
    } catch (Exception $exc) {
        $obj->error = "Error deleting user: " . $mysqli->error;
        echo json_encode($obj);
    }
}
$mysqli->select_db($_POST['databaseName']);

error_log("Installation: ".__LINE__);
/*
  $cmd = "mysql -h {$_POST['databaseHost']} -u {$_POST['databaseUser']} -p {$_POST['databasePass']} {$_POST['databaseName']} < {$_POST['systemRootPath']}install/database.sql";
  exec("{$cmd} 2>&1", $output, $return_val);
  if ($return_val !== 0) {
  $obj->error = "Error on command: {$cmd}";
  echo json_encode($obj);
  exit;
  }
 */
error_log("Installation: ".__LINE__);
if ($_POST['createTables'] > 0) {
    error_log("Installation: ".__LINE__);
    // Temporary variable, used to store current query
    $templine = '';
    $installFile = "{$_POST['systemRootPath']}install/database.sql";
    if (!file_exists($installFile)) {
        $obj->error = "File Not found {$installFile}";
        echo json_encode($obj);
        exit;
    }
    error_log("Installation: ".__LINE__);
    // Read in entire file
    $lines = file($installFile);
    if (empty($lines)) {
        $obj->error = "File is empty {$installFile}";
        echo json_encode($obj);
        exit;
    }
    error_log("Installation: ".__LINE__);
    // Loop through each line
    $obj->error = '';
    foreach ($lines as $line) {
        // Skip it if it's a comment
        if (substr($line, 0, 2) == '--' || $line == '') {
            continue;
        }

        // Add this line to the current segment
        $templine .= $line;
        // If it has a semicolon at the end, it's the end of the query
        if (substr(trim($line), -1, 1) == ';') {
            // Perform the query
            //error_log("Installation: ".$templine);
            try {
                $mysqli->query($templine);
            } catch (Exception $exc) {
                error_log("Installation: SQL ERROR ".$mysqli->error);
                $obj->error = ('Error performing query \'<strong>' . $templine . '\': ' . $mysqli->error . '<br /><br />');
            }

            // Reset temp variable to empty
            $templine = '';
        }
    }
    error_log("Installation: ".__LINE__);
}

error_log("Installation: ".__LINE__);

$sql = "DELETE FROM users WHERE id = 1 ";


try {
    $mysqli->query($sql);
} catch (Exception $exc) {
    $obj->error = "Error deleting user: " . $mysqli->error;
    echo json_encode($obj);
}

error_log("Installation: ".__LINE__);
$sql = "INSERT INTO users (id, user, email, password, created, modified, isAdmin) VALUES (1, 'admin', '" . $_POST['contactEmail'] . "', '" . md5($_POST['systemAdminPass']) . "', now(), now(), true)";

try {
    $mysqli->query($sql);
} catch (Exception $exc) {
    $obj->error = "Error deleting user: " . $mysqli->error;
    echo json_encode($obj);
}

error_log("Installation: ".__LINE__);
$sql = "DELETE FROM categories WHERE id = 1 ";
try {
    $mysqli->query($sql);
} catch (Exception $exc) {
    $obj->error = "Error deleting user: " . $mysqli->error;
    echo json_encode($obj);
}

error_log("Installation: ".__LINE__);
$sql = "INSERT INTO categories (id, name, clean_name, description, created, modified) VALUES (1, 'Default', 'default','', now(), now())";
try {
    $mysqli->query($sql);
} catch (Exception $exc) {
    $obj->error = "Error deleting user: " . $mysqli->error;
    echo json_encode($obj);
}

error_log("Installation: ".__LINE__);
$sql = "DELETE FROM configurations WHERE id = 1 ";
try {
    $mysqli->query($sql);
} catch (Exception $exc) {
    $obj->error = "Error deleting user: " . $mysqli->error;
    echo json_encode($obj);
}

error_log("Installation: ".__LINE__);

$encoder = 'https://encoder1.wwbn.net/';
if (is_dir("{$_POST['systemRootPath']}Encoder")) {
    $encoder = "{$_POST['webSiteRootURL']}Encoder/";
}

$sql = "INSERT INTO configurations (id, video_resolution, users_id, version, webSiteTitle, language, contactEmail, encoderURL,  created, modified) "
        . " VALUES "
        . " (1, '858:480', 1,'{$installationVersion}', '{$_POST['webSiteTitle']}', '{$_POST['mainLanguage']}', '{$_POST['contactEmail']}', '{$encoder}', now(), now())";
 try {
    $mysqli->query($sql);
} catch (Exception $exc) {
    $obj->error = "Error deleting user: " . $mysqli->error;
    echo json_encode($obj);
}

error_log("Installation: ".__LINE__);
$sql = "INSERT INTO `plugins` VALUES (NULL, 'a06505bf-3570-4b1f-977a-fd0e5cab205d', 'active', now(), now(), '', 'Gallery', 'Gallery', '1.0');";
try {
    $mysqli->query($sql);
} catch (Exception $exc) {
    $obj->error = "Error deleting user: " . $mysqli->error;
    echo json_encode($obj);
}

error_log("Installation: ".__LINE__);
$mysqli->close();

if (empty($_POST['salt'])) {
    $_POST['salt'] = uniqid();
}
$content = "<?php
\$global['configurationVersion'] = 3.1;
\$global['disableAdvancedConfigurations'] = 0;
\$global['videoStorageLimitMinutes'] = 0;
\$global['disableTimeFix'] = 0;
\$global['logfile'] = '{$_POST['systemRootPath']}videos/avideo.log';
if(!empty(\$_SERVER['SERVER_NAME']) && \$_SERVER['SERVER_NAME']!=='localhost' && !filter_var(\$_SERVER['SERVER_NAME'], FILTER_VALIDATE_IP)) {
    // get the subdirectory, through CONTEXT_PREFIX if Apache Alias
    // directive is used, or from DOCUMENT_ROOT otherwise
    if (!empty(\$_SERVER['CONTEXT_PREFIX'])) {
        \$subDir = \$_SERVER['CONTEXT_PREFIX'];
    } else {
        \$file = str_replace(\"\\\\\", \"/\", __FILE__);
        \$subDir = str_replace(array(\$_SERVER[\"DOCUMENT_ROOT\"], 'videos/configuration.php'), array('',''), \$file);
    }
    \$global['webSiteRootURL'] = \"http\".(!empty(\$_SERVER['HTTPS'])?\"s\":\"\").\"://\".\$_SERVER['SERVER_NAME'].\$subDir;
}else{
    \$global['webSiteRootURL'] = '{$_POST['webSiteRootURL']}';
}
\$global['systemRootPath'] = '{$_POST['systemRootPath']}';
\$global['salt'] = '{$_POST['salt']}';
\$global['saltV2'] = '"._uniqid()."';
\$global['disableTimeFix'] = 0;
\$global['enableDDOSprotection'] = 1;
\$global['ddosMaxConnections'] = 40;
\$global['ddosSecondTimeout'] = 5;
\$global['strictDDOSprotection'] = 0;
\$global['noDebug'] = 0;
\$global['webSiteRootPath'] = '';
if(empty(\$global['webSiteRootPath'])){
    preg_match('/https?:\/\/[^\/]+(.*)/i', \$global['webSiteRootURL'], \$matches);
    if(!empty(\$matches[1])){
        \$global['webSiteRootPath'] = \$matches[1];
    }
}
if(empty(\$global['webSiteRootPath'])){
    die('Please configure your webSiteRootPath');
}

\$mysqlHost = '{$_POST['databaseHost']}';
\$mysqlPort = '{$_POST['databasePort']}';
\$mysqlUser = '{$_POST['databaseUser']}';
\$mysqlPass = '{$_POST['databasePass']}';
\$mysqlDatabase = '{$_POST['databaseName']}';

//\$global['stopBotsList'] = array('headless', 'bot','spider','rouwler','Nuclei','MegaIndex','NetSystemsResearch','CensysInspect','slurp','crawler','curl','fetch','loader');
//\$global['stopBotsWhiteList'] = array('facebook','google','bing','yahoo','yandex','twitter');

/**
 * Do NOT change from here
 */

require_once \$global['systemRootPath'].'objects/include_config.php';
";

$videosDir = $_POST['systemRootPath'].'videos/';

if(!is_dir($videosDir)){
    mkdir($videosDir, 0777, true);
}

error_log("Installation: ".__LINE__);
$fp = fopen("{$videosDir}configuration.php", "wb");
fwrite($fp, $content);
fclose($fp);
error_log("Installation: ".__LINE__);
/*
//copy the 100% progress sample file to be used when the uploaded file is already encoded in the MP4 or WBM formats
exec("cp {$_POST['systemRootPath']}install/FinishedProgressSample.* {$_POST['systemRootPath']}videos/", $output, $return_val);

if ($return_val !== 0) {
    $obj->error = "Error copying the encoding progress sample files. Check whether the directory {$_POST['systemRootPath']}videos/ exists and the process have permission";
    echo json_encode($obj);
    exit;
}
*/

/*
FOR WWBNIndex Plugin
*/
$systemRootPath = __DIR__ . DIRECTORY_SEPARATOR . '../';
$file = "{$systemRootPath}plugin/WWBNIndex/submitIndex.php";
if (file_exists($file)) {
    include $file;
}
error_log("Installation: ".__LINE__);
$obj->success = true;
echo json_encode($obj);

error_log("Installation: ".__LINE__);
