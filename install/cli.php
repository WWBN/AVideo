<?php

$_POST["systemRootPath"] = "/var/www/html/AVideo/";
$_POST["databaseHost"] = getenv("DB_MYSQL_HOST");
$_POST["databasePort"] = getenv("DB_MYSQL_PORT");
$_POST["databaseName"] = getenv("DB_MYSQL_NAME");
$_POST["databaseUser"] = getenv("DB_MYSQL_USER");
$_POST["databasePass"] = getenv("DB_MYSQL_PASSWORD");
$_POST["createTables"] = 1;
$_POST["contactEmail"] = getenv("CONTACT_EMAIL");
$_POST["systemAdminPass"] = getenv("SYSTEM_ADMIN_PASSWORD");
$_POST["webSiteTitle"] = getenv("WEBSITE_TITLE");
$_POST["mainLanguage"] = getenv("MAIN_LANGUAGE");
$_POST["webSiteRootURL"] = "https://".getenv("SERVER_NAME")."/";
if (getenv("ENCODER_URL") !== false) {
  $_POST["encoderURL"] = getenv("ENCODER_URL");
}

require_once "./checkConfiguration.php";
