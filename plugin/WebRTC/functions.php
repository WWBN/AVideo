<?php
/**
 * Encrypts a string using AES-256-CBC
 *
 * @param string $plaintext The plaintext to be encrypted
 * @param string $salt      The salt used to generate the 32-byte key and 16-byte IV
 * @return string           The ciphertext in Base64 format
 */
function encrypt_data($plaintext, $salt)
{
    // Generate a 32-byte key in binary (raw data)
    $key = hash('sha256', $salt, true); // 32 bytes

    // The IV is taken from the first 16 bytes of the key
    $iv = substr($key, 0, 16); // 16 bytes

    // Perform the encryption
    $ciphertext = openssl_encrypt(
        $plaintext,
        'aes-256-cbc',
        $key,
        OPENSSL_RAW_DATA, // Process in binary form (not Base64)
        $iv
    );

    // Convert the binary ciphertext to Base64 for easy storage/transport
    $ciphertextB64 = base64_encode($ciphertext);
    return $ciphertextB64;
}

/**
 * Decrypts a Base64 string using AES-256-CBC
 *
 * @param string $ciphertextB64 The ciphertext in Base64 format
 * @param string $salt          The same salt used in encrypt_data()
 * @return string               The decrypted plaintext
 */
function decrypt_data($ciphertextB64, $salt)
{
    // Generate the same 32-byte key
    $key = hash('sha256', $salt, true); // 32 bytes
    $iv = substr($key, 0, 16);          // 16 bytes

    // Convert from Base64 to binary
    $ciphertext = base64_decode($ciphertextB64);

    // Decrypt
    $plaintext = openssl_decrypt(
        $ciphertext,
        'aes-256-cbc',
        $key,
        OPENSSL_RAW_DATA, // Ciphertext is in binary form
        $iv
    );

    return $plaintext;
}

function getWebRTCInfo(){
    global $global;
    $file = "{$global['systemRootPath']}plugin/WebRTC/WebRTC2RTMP.json";
    if(!file_exists($file)){
        return false;
    }
    $content = file_get_contents("{$global['systemRootPath']}plugin/WebRTC/WebRTC2RTMP.json");
    if(empty($content)){
        return false;
    }
    $json = json_decode($content);
    if(empty($json)){
        return false;
    }
    return $json;
}

function getWebRTC2RTMPURL(){
    $json = getWebRTCInfo();
    if(empty($json)){
        return '';
    }
    return "https://{$json->domain}:{$json->serverPort}";
}