<?php
require_once "../videos/configuration.php";
require_once "mysql_dal.php";
$dsn      = 'mysql:dbname='.$mysqlDatabase.';host='.$mysqlHost;

// error reporting (this is a demo, after all!)
ini_set('display_errors',1);error_reporting(E_ALL);

// Autoloading (composer is preferred, but for this example let's just do this)
require_once('OAuth2/Autoloader.php');
OAuth2\Autoloader::register();
require_once('oauthstorage.php');
// $dsn is the Data Source Name for your database, for exmaple "mysql:dbname=my_oauth2_db;host=localhost"
$storage = new OAuth2\Storage\oauthStorage(array('dsn' => $dsn, 'username' => $mysqlUser, 'password' => $mysqlPass));

// Pass a storage object or array of storage objects to the OAuth2 server class
$server = new OAuth2\Server($storage);

// Add the "Client Credentials" grant type (it is the simplest of the grant types)
$server->addGrantType(new OAuth2\GrantType\ClientCredentials($storage));

// Add the "Authorization Code" grant type (this is where the oauth magic happens)
$server->addGrantType(new OAuth2\GrantType\AuthorizationCode($storage));

?>
