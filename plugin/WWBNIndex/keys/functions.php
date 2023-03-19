<?php
include_once dirname(__FILE__).'/../../../vendor/autoload.php';

use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\RSA\Formats\Keys\PKCS1;

error_reporting(0);
//error_reporting(E_ALL);

function wwbnIndexCreateKeys($UserIDPacket = 'Test <test@example.com>', $password = '') 
{
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

    $uid = new OpenPGP_UserIDPacket($UserIDPacket);

    $wkey = new OpenPGP_Crypt_RSA($nkey);
    $m = $wkey->sign_key_userid([$nkey, $uid]);
    $m[0] = OpenPGP_Crypt_Symmetric::encryptSecretKey($password, $nkey);

    // Serialize private key
    $private_bytes = $m->to_bytes();

    // Serialize public key message
    $pubm = clone($m);
    $pubm[0] = new OpenPGP_PublicKeyPacket($pubm[0]);
    $public_bytes = $pubm->to_bytes();

    return [
        'private_bytes' => $private_bytes,
        'private' => OpenPGP::enarmor($private_bytes, "PGP PRIVATE KEY BLOCK"),
        'public_bytes' => $public_bytes,
        'public' => OpenPGP::enarmor($public_bytes, "PGP PUBLIC KEY BLOCK"),
    ];
}

function wwbnIndexEncryptMessage($message, $publicKey)
{
    $keyBytes = OpenPGP::unarmor($publicKey, "PGP PUBLIC KEY BLOCK");
    $key = OpenPGP_Message::parse($keyBytes);
    $data = new OpenPGP_LiteralDataPacket($message, ['format' => 'u']);
    $encrypted = OpenPGP_Crypt_Symmetric::encrypt($key, new OpenPGP_Message([$data]));

    $encryptedMessage = OpenPGP::enarmor($encrypted->to_bytes(), "PGP MESSAGE");
    return ['encryptedMessage_bytes' => $encrypted->to_bytes(), 'encryptedMessage' => $encryptedMessage];
}

function wwbnIndexDecryptMessage($encryptedMessage, $privatekey, $password = '')
{
    $keyBytes = OpenPGP::unarmor($privatekey, 'PGP PRIVATE KEY BLOCK');
    $key = OpenPGP_Message::parse($keyBytes);
    // Try each secret key packet
    if (empty($key)) {
        return false;
    }
    foreach ($key as $p) {
        if (!($p instanceof OpenPGP_SecretKeyPacket)) {
            continue;
        }
        $key = OpenPGP_Crypt_Symmetric::decryptSecretKey($password, $p);

        $msg = OpenPGP_Message::parse(OpenPGP::unarmor($encryptedMessage, 'PGP MESSAGE'));

        $decryptor = new OpenPGP_Crypt_RSA($key);
        $decrypted = $decryptor->decrypt($msg);

        if (!empty($decrypted->packets) && !empty($decrypted->packets[0]->data)) {
            return $decrypted->packets[0]->data;
        }
    }
}
