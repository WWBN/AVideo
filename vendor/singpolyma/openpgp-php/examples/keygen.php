<?php

@include_once dirname(__FILE__).'/../vendor/autoload.php';
require_once dirname(__FILE__).'/../lib/openpgp.php';
require_once dirname(__FILE__).'/../lib/openpgp_crypt_rsa.php';

$rsa = new \phpseclib\Crypt\RSA();
$k = $rsa->createKey(512);
$rsa->loadKey($k['privatekey']);

$nkey = new OpenPGP_SecretKeyPacket(array(
   'n' => $rsa->modulus->toBytes(),
   'e' => $rsa->publicExponent->toBytes(),
   'd' => $rsa->exponent->toBytes(),
   'p' => $rsa->primes[2]->toBytes(),
   'q' => $rsa->primes[1]->toBytes(),
   'u' => $rsa->coefficients[2]->toBytes()
));

$uid = new OpenPGP_UserIDPacket('Test <test@example.com>');

$wkey = new OpenPGP_Crypt_RSA($nkey);
$m = $wkey->sign_key_userid(array($nkey, $uid));

// Serialize private key
print $m->to_bytes();

// Serialize public key message
$pubm = clone($m);
$pubm[0] = new OpenPGP_PublicKeyPacket($pubm[0]);

$public_bytes = $pubm->to_bytes();
