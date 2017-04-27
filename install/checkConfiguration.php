<?php

$installationVersion = "2.1";

header('Content-Type: application/json');

$obj = new stdClass();
$obj->post = $_POST;

if (!file_exists($_POST['systemRootPath'] . "index.php")) {
    $obj->error = "Your system path to application ({$_POST['systemRootPath']}) is wrong";
    echo json_encode($obj);
    exit;
}

$mysqli = @new mysqli($_POST['databaseHost'], $_POST['databaseUser'], $_POST['databasePass']);

/*
 * This is the "official" OO way to do it,
 * BUT $connect_error was broken until PHP 5.2.9 and 5.3.0.
 */
if ($mysqli->connect_error) {
    $obj->error = ('Connect Error (' . $mysqli->connect_errno . ') ' . $mysqli->connect_error);
    echo json_encode($obj);
    exit;
}

if ($_POST['createTables'] == 2) {
    $sql = "CREATE DATABASE IF NOT EXISTS {$_POST['databaseName']}";
    if ($mysqli->query($sql) !== TRUE) {
        $obj->error = "Error creating database: " . $mysqli->error;
        echo json_encode($obj);
        exit;
    }
}
$mysqli->select_db($_POST['databaseName']);

/*
  $cmd = "mysql -h {$_POST['databaseHost']} -u {$_POST['databaseUser']} -p {$_POST['databasePass']} {$_POST['databaseName']} < {$_POST['systemRootPath']}install/database.sql";
  exec("{$cmd} 2>&1", $output, $return_val);
  if ($return_val !== 0) {
  $obj->error = "Error on command: {$cmd}";
  echo json_encode($obj);
  exit;
  }
 */
if ($_POST['createTables'] > 0) {
// Temporary variable, used to store current query
    $templine = '';
// Read in entire file
    $lines = file("{$_POST['systemRootPath']}install/database.sql");
// Loop through each line
    $obj->error = "";
    foreach ($lines as $line) {
// Skip it if it's a comment
        if (substr($line, 0, 2) == '--' || $line == '')
            continue;

// Add this line to the current segment
        $templine .= $line;
// If it has a semicolon at the end, it's the end of the query
        if (substr(trim($line), -1, 1) == ';') {
            // Perform the query
            if (!$mysqli->query($templine)) {
                $obj->error = ('Error performing query \'<strong>' . $templine . '\': ' . $mysqli->error . '<br /><br />');
            }
            // Reset temp variable to empty
            $templine = '';
        }
    }
}


$sql = "DELETE FROM users WHERE id = 1 ";
if ($mysqli->query($sql) !== TRUE) {
    $obj->error = "Error deleting user: " . $mysqli->error;
    echo json_encode($obj);
    exit;
}

$sql = "INSERT INTO users (id, user, password, created, modified, isAdmin) VALUES (1, 'admin', '" . md5($_POST['systemAdminPass']) . "', now(), now(), true)";
if ($mysqli->query($sql) !== TRUE) {
    $obj->error = "Error creating admin user: " . $mysqli->error;
    echo json_encode($obj);
    exit;
}

$sql = "DELETE FROM categories WHERE id = 1 ";
if ($mysqli->query($sql) !== TRUE) {
    $obj->error = "Error deleting category: " . $mysqli->error;
    echo json_encode($obj);
    exit;
}

$sql = "INSERT INTO categories (id, name, clean_name, created, modified) VALUES (1, 'Default', 'default', now(), now())";
if ($mysqli->query($sql) !== TRUE) {
    $obj->error = "Error creating category: " . $mysqli->error;
    echo json_encode($obj);
    exit;
}

$sql = "DELETE FROM configurations WHERE id = 1 ";
if ($mysqli->query($sql) !== TRUE) {
    $obj->error = "Error deleting configuration: " . $mysqli->error;
    echo json_encode($obj);
    exit;
}

$sql = "INSERT INTO configurations (id, video_resolution, users_id, version, webSiteTitle, language, contactEmail,  created, modified) VALUES (1, '858:480', 1,'{$installationVersion}', '{$_POST['webSiteTitle']}', '{$_POST['mainLanguage']}', '{$_POST['contactEmail']}', now(), now())";
if ($mysqli->query($sql) !== TRUE) {
    $obj->error = "Error creating configuration: " . $mysqli->error;
    echo json_encode($obj);
    exit;
}

$mysqli->close();

$content = "<?php
\$global['webSiteRootURL'] = '{$_POST['webSiteRootURL']}';
\$global['systemRootPath'] = '{$_POST['systemRootPath']}';


\$mysqlHost = '{$_POST['databaseHost']}';
\$mysqlUser = '{$_POST['databaseUser']}';
\$mysqlPass = '{$_POST['databasePass']}';
\$mysqlDatabase = '{$_POST['databaseName']}';

/**
 * Do NOT change from here
 */
session_start();

\$global['mysqli'] = new mysqli(\$mysqlHost, \$mysqlUser,\$mysqlPass,\$mysqlDatabase);
require_once \$global['systemRootPath'].'locale/function.php';
require_once \$global['systemRootPath'].'objects/include_config.php';
";

$fp = fopen($_POST['systemRootPath'] . "videos/configuration.php", "wb");
fwrite($fp, $content);
fclose($fp);

$obj->success = true;
echo json_encode($obj);
