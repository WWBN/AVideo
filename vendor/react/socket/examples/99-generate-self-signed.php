<?php

// A very simple helper script used to generate self-signed certificates.
// Accepts the CN and an optional passphrase to encrypt the private key.
//
// $ php examples/99-generate-self-signed.php localhost my-secret-passphrase > secret.pem

// certificate details (Distinguished Name)
// (OpenSSL applies defaults to missing fields)
$dn = array(
    "commonName" => isset($argv[1]) ? $argv[1] : "localhost",
//     "countryName" => "AU",
//     "stateOrProvinceName" => "Some-State",
//     "localityName" => "London",
//     "organizationName" => "Internet Widgits Pty Ltd",
//     "organizationalUnitName" => "R&D",
//     "emailAddress" => "admin@example.com"
);

// create certificate which is valid for ~10 years
$privkey = openssl_pkey_new();
$cert = openssl_csr_new($dn, $privkey);
$cert = openssl_csr_sign($cert, null, $privkey, 3650);

// export public and (optionally encrypted) private key in PEM format
openssl_x509_export($cert, $out);
echo $out;

$passphrase = isset($argv[2]) ? $argv[2] : null;
openssl_pkey_export($privkey, $out, $passphrase);
echo $out;
