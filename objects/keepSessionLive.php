<?php
session_start();
$_SESSION['lastUpdate'] = time();
session_write_close();