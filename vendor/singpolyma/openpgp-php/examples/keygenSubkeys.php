<?php

use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\RSA\Formats\Keys\PKCS1;

@include_once dirname(__FILE__).'/../vendor/autoload.php';
require_once dirname(__FILE__).'/../lib/openpgp.php';
require_once dirname(__FILE__).'/../lib/openpgp_crypt_rsa.php';

// Key length: 512, 1024, 2048, 3072, 4096
$key_length = 512;

// Generate a master signing key
$privateKey = RSA::createKey(512);
$privateKeyComponents = PKCS1::load($privateKey->toString('PKCS1'));

$nkey = new OpenPGP_SecretKeyPacket(array(
   'n' => $privateKeyComponents["modulus"]->toBytes(),
   'e' => $privateKeyComponents["publicExponent"]->toBytes(),
   'd' => $privateKeyComponents["privateExponent"]->toBytes(),
   'p' => $privateKeyComponents["primes"][1]->toBytes(),
   'q' => $privateKeyComponents["primes"][2]->toBytes(),
   'u' => $privateKeyComponents["coefficients"][2]->toBytes()
));

// Start assembling packets for our eventual OpenPGP_Message
$packets = array($nkey);

$wkey = new OpenPGP_Crypt_RSA($nkey);
$fingerprint = $wkey->key()->fingerprint;
$key = $wkey->private_key();
$key = $key->withHash('sha256');
$keyid = substr($fingerprint, -16);

// Add multiple UID packets and signatures

$uids = array(
	new OpenPGP_UserIDPacket('Support', '', 'support@example.com'),
	new OpenPGP_UserIDPacket('Security', '', 'security@example.com'),
);

foreach($uids as $uid) {
	// Append the UID packet
	$packets[] = $uid;
	
	$sig = new OpenPGP_SignaturePacket(new OpenPGP_Message(array($nkey, $uid)), 'RSA', 'SHA256');
	$sig->signature_type = 0x13;
	$sig->hashed_subpackets[] = new OpenPGP_SignaturePacket_KeyFlagsPacket(array(0x01 | 0x02)); // Certify + sign bits
	$sig->hashed_subpackets[] = new OpenPGP_SignaturePacket_IssuerPacket($keyid);
	$m = $wkey->sign_key_userid(array($nkey, $uid, $sig));
	
	// Append the UID signature from the master key
	$packets[] = $m->packets[2];
}

// Generate an encryption subkey

$rsa_subkey = RSA::createKey(512);
$privateKeyComponents = PKCS1::load($rsa_subkey->toString('PKCS1'));

$subkey = new OpenPGP_SecretKeyPacket(array(
   'n' => $privateKeyComponents["modulus"]->toBytes(),
   'e' => $privateKeyComponents["publicExponent"]->toBytes(),
   'd' => $privateKeyComponents["privateExponent"]->toBytes(),
   'p' => $privateKeyComponents["primes"][2]->toBytes(),
   'q' => $privateKeyComponents["primes"][1]->toBytes(),
   'u' => $privateKeyComponents["coefficients"][2]->toBytes()
));

// Append the encryption subkey
$packets[] = $subkey;

$sub_wkey = new OpenPGP_Crypt_RSA($subkey);

/*
 * Sign the encryption subkey with the master key
 *
 * OpenPGP_SignaturePacket assumes any message starting with an
 * OpenPGP_PublicKeyPacket is followed by a OpenPGP_UserIDPacket. We need
 * to pass `null` in the constructor and generate the `->data` ourselves.
 */
$sub_sig = new OpenPGP_SignaturePacket(null, 'RSA', 'SHA256');
$sub_sig->signature_type = 0x18;
$sub_sig->hashed_subpackets[] = new OpenPGP_SignaturePacket_SignatureCreationTimePacket(time());
$sub_sig->hashed_subpackets[] = new OpenPGP_SignaturePacket_KeyFlagsPacket(array(0x0C)); // Encrypt bits
$sub_sig->hashed_subpackets[] = new OpenPGP_SignaturePacket_IssuerPacket($keyid);
$sub_sig->data = implode('', $nkey->fingerprint_material()) . implode('', $subkey->fingerprint_material());
$sub_sig->sign_data(array('RSA' => array('SHA256' => function($data) use($key) {return array($key->sign($data));})));

// Append the subkey signature
$packets[] = $sub_sig;

// Build the OpenPGP_Message for the secret key from our packets
$m = new OpenPGP_Message($packets);

// Serialize the private key
print $m->to_bytes();

// Clone a public key message from the secret key
$pubm = clone($m);

// Convert the private key packets to public so we only export public data
// (n+e in RSA)
foreach($pubm as $idx => $p) {
	if($p instanceof OpenPGP_SecretSubkeyPacket) {
		$pubm[$idx] = new OpenPGP_PublicSubkeyPacket($p);
	} else if($p instanceof OpenPGP_SecretKeyPacket) {
		$pubm[$idx] = new OpenPGP_PublicKeyPacket($p);
	}
}

// Serialize the public key
$public_bytes = $pubm->to_bytes();

// Note: If using PHP 7.4 CLI, disable deprecated warnings:
// php -d error_reporting="E_ALL & ~E_DEPRECATED" examples/keygenSubkeys.php > mykey.gpg
