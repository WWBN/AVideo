<?php

use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\RSA\Formats\Keys\PKCS1;

@include_once dirname(__FILE__).'/../vendor/autoload.php';
require_once dirname(__FILE__).'/../lib/openpgp.php';
require_once dirname(__FILE__).'/../lib/openpgp_crypt_rsa.php';
require_once dirname(__FILE__).'/../lib/openpgp_crypt_symmetric.php';

$privateKey = RSA::createKey(512);
$publickey = $privateKey->getPublicKey();

$privateKeyComponents = PKCS1::load($privateKey->toString('PKCS1'));

$nkey = new OpenPGP_SecretKeyPacket(array(
   'n' => $privateKeyComponents["modulus"]->toBytes(),
   'e' => $privateKeyComponents["publicExponent"]->toBytes(),
   'd' => $privateKeyComponents["privateExponent"]->toBytes(),
   'p' => $privateKeyComponents["primes"][1]->toBytes(),
   'q' => $privateKeyComponents["primes"][2]->toBytes(),
   'u' => $privateKeyComponents["coefficients"][2]->toBytes()
));

$uid = new OpenPGP_UserIDPacket('Test <test@example.com>');

$wkey = new OpenPGP_Crypt_RSA($nkey);
$m = $wkey->sign_key_userid(array($nkey, $uid));
$m[0] = OpenPGP_Crypt_Symmetric::encryptSecretKey("password", $nkey);

// Serialize encrypted private key
print $m->to_bytes();
