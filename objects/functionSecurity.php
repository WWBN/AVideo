<?php
/*
secure salt in PHP using standard characters and numbers.
This code will generate a 10 to 32-character string
*/
function _uniqid() {
    // Generate 16 bytes of random data
    $randomBytes = random_bytes(16);

    // Convert the binary data to a hexadecimal string
    $hex = bin2hex($randomBytes);

    // If you want a variable length output, you can truncate the MD5 hash
    // For example, to get a random length between 10 and 32 characters:
    $randomLength = rand(10, 32);
    $randomString = substr($hex, 0, $randomLength);

    return $randomString;
}
?>