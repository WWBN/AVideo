<?php

$counter = 0;
$sleep = 5;
$timeout = 60;
$connected = false;
$db_host = getenv("DB_MYSQL_HOST");
$db_port = getenv("DB_MYSQL_PORT");
$db_name = getenv("DB_MYSQL_NAME");
$db_user = getenv("DB_MYSQL_USER");
$db_pass = getenv("DB_MYSQL_PASSWORD");

while (!$connected) {
  echo "Checking database connection....";
  $mysqli = @new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);
  if ($mysqli !== false && $mysqli->connect_error === null) {
    echo "OK\n";
    $connected = true;
  } else {
    $counter ++;
    echo "Failed (attempt ".$counter.")\n";
    if ($counter*$sleep > $timeout) {
      echo "Giving up... (".$mysqli->connect_errno.") ".$mysqli->connect_error;
      exit(1);
    }
    
    sleep($sleep);
  }
}

exit(0);
