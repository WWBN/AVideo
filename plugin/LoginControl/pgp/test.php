<?php

require_once dirname(__FILE__) . '/../../../plugin/LoginControl/pgp/functions.php';

$message = 'teste 1234';

$pass = time();
$keys = createKeys('Test <test@example.com>', $pass);
$public = $keys['public'];
$private = $keys['private'];


echo '<pre>';
echo '<hr><h1>Key Password</h1><br>' . PHP_EOL;
echo $pass;

echo '<hr><h1>Public</h1><br>' . PHP_EOL;
echo $public;

echo '<hr><h1>Private</h1><br>' . PHP_EOL;
echo $private;

echo '<hr><h1>Encrypt</h1><br>' . PHP_EOL;
$encMessage = encryptMessage($message, $public);
echo $encMessage["encryptedMessage"];

echo '<hr><h1>Decrypt</h1><br>' . PHP_EOL;
echo (decryptMessage($encMessage['encryptedMessage'], $private, $pass));

echo '</pre>';
