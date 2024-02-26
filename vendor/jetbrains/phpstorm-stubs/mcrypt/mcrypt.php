<?php

// Start of mcrypt v.
use JetBrains\PhpStorm\Deprecated;

/**
 * Encrypt/decrypt data in ECB mode
 * @link https://php.net/manual/en/function.mcrypt-ecb.php
 * @param string|int $cipher
 * @param string $key
 * @param string $data
 * @param int $mode
 * @return string
 * @removed 7.0
 */
#[Deprecated(since: "5.5")]
function mcrypt_ecb ($cipher, $key, $data, $mode) {}

/**
 * Encrypt/decrypt data in CBC mode
 * @link https://php.net/manual/en/function.mcrypt-cbc.php
 * @param int|string $cipher
 * @param string $key
 * @param string $data
 * @param int $mode
 * @param string $iv [optional]
 * @return string
 * @removed 7.0
 */
#[Deprecated(since: "5.5")]
function mcrypt_cbc ($cipher, $key, $data, $mode, $iv = null) {}

/**
 * Encrypt/decrypt data in CFB mode
 * @link https://php.net/manual/en/function.mcrypt-cfb.php
 * @param int|string $cipher
 * @param string $key
 * @param string $data
 * @param int $mode
 * @param string $iv [optional]
 * @return string
 * @removed 7.0
 */
#[Deprecated(since: '5.5')]
function mcrypt_cfb ($cipher, $key, $data, $mode, $iv = null) {}

/**
 * Encrypt/decrypt data in OFB mode
 * @link https://php.net/manual/en/function.mcrypt-ofb.php
 * @param int|string $cipher
 * @param string $key
 * @param string $data
 * @param int $mode
 * @param string $iv [optional]
 * @return string
 * @removed 7.0
 */
#[Deprecated(since: '5.5')]
function mcrypt_ofb ($cipher, $key, $data, $mode, $iv = null) {}

/**
 * Get the key size of the specified cipher
 * @link https://php.net/manual/en/function.mcrypt-get-key-size.php
 * @param int|string $cipher
 * @param string $mode
 * @return int
 */
#[Deprecated(since: '7.1')]
function mcrypt_get_key_size ($cipher, $mode) {}

/**
 * Get the block size of the specified cipher
 * @link https://php.net/manual/en/function.mcrypt-get-block-size.php
 * @param string|int $cipher <p>
 * One of the MCRYPT_ciphername constants or the name
 * of the algorithm as string.
 * </p>
 * @param string $mode <p>
 * One of the <b>MCRYPT_MODE_modename</b> constants, or one of the following strings: "ecb", "cbc", "cfb", "ofb", "nofb" or "stream".</p>
 * @return int Gets the block size, as an integer.
 */
#[Deprecated(since: '7.1')]
function mcrypt_get_block_size ($cipher, $mode) {}

/**
 * Get the name of the specified cipher
 * @link https://php.net/manual/en/function.mcrypt-get-cipher-name.php
 * @param int|string $cipher <p>
 * One of the MCRYPT_ciphername constants or the name
 * of the algorithm as string.
 * </p>
 * @return string|false This function returns the name of the cipher or false, if the cipher does
 * not exist.
 */
#[Deprecated(since: '7.1')]
function mcrypt_get_cipher_name ($cipher) {}

/**
 * Creates an initialization vector (IV) from a random source
 * @link https://php.net/manual/en/function.mcrypt-create-iv.php
 * @param int $size <p>
 * Determines the size of the IV, parameter source
 * (defaults to random value) specifies the source of the IV.
 * </p>
 * @param int $source [optional] <p>
 * The source can be MCRYPT_RAND (system random
 * number generator), MCRYPT_DEV_RANDOM (read
 * data from /dev/random) and
 * MCRYPT_DEV_URANDOM (read data from
 * /dev/urandom). MCRYPT_RAND
 * is the only one supported on Windows because Windows (of course)
 * doesn't have /dev/random or
 * /dev/urandom.
 * </p>
 * <p>
 * When using MCRYPT_RAND, remember to call
 * srand before
 * mcrypt_create_iv to initialize the random
 * number generator; it is not seeded automatically like
 * rand is.
 * </p>
 * @return string|false the initialization vector, or false on error.
 */
#[Deprecated(since: '7.1')]
function mcrypt_create_iv ($size, $source = MCRYPT_DEV_URANDOM) {}

/**
 * Gets an array of all supported ciphers
 * @link https://php.net/manual/en/function.mcrypt-list-algorithms.php
 * @param string $lib_dir [optional] <p>
 * Specifies the directory where all algorithms are located. If not
 * specifies, the value of the mcrypt.algorithms_dir &php.ini; directive
 * is used.
 * </p>
 * @return array an array with all the supported algorithms.
 */
#[Deprecated(since: '7.1')]
function mcrypt_list_algorithms ($lib_dir = null) {}

/**
 * Gets an array of all supported modes
 * @link https://php.net/manual/en/function.mcrypt-list-modes.php
 * @param string $lib_dir [optional] <p>
 * Specifies the directory where all modes are located. If not
 * specifies, the value of the mcrypt.modes_dir
 * &php.ini; directive is used.
 * </p>
 * @return array an array with all the supported modes.
 */
#[Deprecated(since: '7.1')]
function mcrypt_list_modes ($lib_dir = null) {}

/**
 * Returns the size of the IV belonging to a specific cipher/mode combination
 * @link https://php.net/manual/en/function.mcrypt-get-iv-size.php
 * @param string $cipher <p>
 * One of the MCRYPT_ciphername constants of the name
 * of the algorithm as string.
 * </p>
 * @param string $mode <p>
 * mode is one of the MCRYPT_MODE_modename constants
 * or one of "ecb", "cbc", "cfb", "ofb", "nofb" or "stream". The IV is
 * ignored in ECB mode as this mode does not require it. You will need to
 * have the same IV (think: starting point) both at encryption and
 * decryption stages, otherwise your encryption will fail.
 * </p>
 * @return int|false the size of the Initialisation Vector (IV) in bytes. On error the
 * function returns false. If the IV is ignored in the specified cipher/mode
 * combination zero is returned.
 */
#[Deprecated(since: '7.1')]
function mcrypt_get_iv_size ($cipher, $mode) {}

/**
 * Encrypts plaintext with given parameters
 * @link https://php.net/manual/en/function.mcrypt-encrypt.php
 * @param string $cipher <p>
 * One of the MCRYPT_ciphername
 * constants of the name of the algorithm as string.
 * </p>
 * @param string $key <p>
 * The key with which the data will be encrypted. If it's smaller that
 * the required keysize, it is padded with '\0'. It is
 * better not to use ASCII strings for keys.
 * </p>
 * <p>
 * It is recommended to use the mhash functions to create a key from a
 * string.
 * </p>
 * @param string $data <p>
 * The data that will be encrypted with the given cipher and mode. If the
 * size of the data is not n * blocksize, the data will be padded with
 * '\0'.
 * </p>
 * <p>
 * The returned crypttext can be larger that the size of the data that is
 * given by data.
 * </p>
 * @param string $mode <p>
 * One of the MCRYPT_MODE_modename
 * constants of one of "ecb", "cbc", "cfb", "ofb", "nofb" or
 * "stream".
 * </p>
 * @param string $iv [optional] <p>
 * Used for the initialisation in CBC, CFB, OFB modes, and in some
 * algorithms in STREAM mode. If you do not supply an IV, while it is
 * needed for an algorithm, the function issues a warning and uses an
 * IV with all bytes set to '\0'.
 * </p>
 * @return string the encrypted data, as a string.
 */
#[Deprecated(since: '7.1')]
function mcrypt_encrypt ($cipher, $key, $data, $mode, $iv = null) {}

/**
 * Decrypts crypttext with given parameters
 * @link https://php.net/manual/en/function.mcrypt-decrypt.php
 * @param string $cipher <p>
 * cipher is one of the MCRYPT_ciphername constants
 * of the name of the algorithm as string.
 * </p>
 * @param string $key <p>
 * key is the key with which the data is encrypted.
 * If it's smaller that the required keysize, it is padded with
 * '\0'.
 * </p>
 * @param string $data <p>
 * data is the data that will be decrypted with
 * the given cipher and mode. If the size of the data is not n * blocksize,
 * the data will be padded with '\0'.
 * </p>
 * @param string $mode <p>
 * mode is one of the MCRYPT_MODE_modename
 * constants of one of "ecb", "cbc", "cfb", "ofb", "nofb" or "stream".
 * </p>
 * @param string $iv [optional] <p>
 * The iv parameter is used for the initialisation
 * in CBC, CFB, OFB modes, and in some algorithms in STREAM mode. If you
 * do not supply an IV, while it is needed for an algorithm, the function
 * issues a warning and uses an IV with all bytes set to
 * '\0'.
 * </p>
 * @return string the decrypted data as a string.
 */
#[Deprecated(since: '7.1')]
function mcrypt_decrypt ($cipher, $key, $data, $mode, $iv = null) {}

/**
 * Opens the module of the algorithm and the mode to be used
 * @link https://php.net/manual/en/function.mcrypt-module-open.php
 * @param string $algorithm <p>
 * The algorithm to be used.
 * </p>
 * @param string $algorithm_directory <p>
 * The algorithm_directory and
 * mode_directory are used to locate the encryption
 * modules. When you supply a directory name, it is used. When you set one
 * of these to the empty string (""), the value set by
 * the mcrypt.algorithms_dir or
 * mcrypt.modes_dir ini-directive is used. When
 * these are not set, the default directories that are used are the ones
 * that were compiled in into libmcrypt (usually
 * /usr/local/lib/libmcrypt).
 * </p>
 * @param string $mode <p>
 * The mode to be used.
 * </p>
 * @param string $mode_directory <p>
 * </p>
 * @return resource|false Normally it returns an encryption descriptor, or false on error.
 */
#[Deprecated(since: '7.1')]
function mcrypt_module_open ($algorithm, $algorithm_directory, $mode, $mode_directory) {}

/**
 * This function initializes all buffers needed for encryption
 * @link https://php.net/manual/en/function.mcrypt-generic-init.php
 * @param resource $td <p>
 * The encryption descriptor.
 * </p>
 * @param string $key <p>
 * The maximum length of the key should be the one obtained by calling
 * mcrypt_enc_get_key_size and every value smaller
 * than this is legal.
 * </p>
 * @param string $iv <p>
 * The IV should normally have the size of the algorithms block size, but
 * you must obtain the size by calling
 * mcrypt_enc_get_iv_size. IV is ignored in ECB. IV
 * MUST exist in CFB, CBC, STREAM, nOFB and OFB modes. It needs to be
 * random and unique (but not secret). The same IV must be used for
 * encryption/decryption. If you do not want to use it you should set it
 * to zeros, but this is not recommended.
 * </p>
 * @return int|false The function returns a negative value on error, -3 when the key length
 * was incorrect, -4 when there was a memory allocation problem and any
 * other return value is an unknown error. If an error occurs a warning will
 * be displayed accordingly. false is returned if incorrect parameters
 * were passed.
 */
#[Deprecated(since: '7.1')]
function mcrypt_generic_init ($td, $key, $iv) {}

/**
 * This function encrypts data
 * @link https://php.net/manual/en/function.mcrypt-generic.php
 * @param resource $td <p>
 * The encryption descriptor.
 * </p>
 * <p>
 * The encryption handle should always be initialized with
 * mcrypt_generic_init with a key and an IV before
 * calling this function. Where the encryption is done, you should free the
 * encryption buffers by calling mcrypt_generic_deinit.
 * See mcrypt_module_open for an example.
 * </p>
 * @param string $data <p>
 * The data to encrypt.
 * </p>
 * @return string the encrypted data.
 */
#[Deprecated(since: '7.1')]
function mcrypt_generic ($td, $data) {}

/**
 * Decrypts data
 * @link https://php.net/manual/en/function.mdecrypt-generic.php
 * @param resource $td <p>
 * An encryption descriptor returned by
 * mcrypt_module_open
 * </p>
 * @param string $data <p>
 * Encrypted data.
 * </p>
 * @return string
 */
#[Deprecated(since: '7.1')]
function mdecrypt_generic ($td, $data) {}

/**
 * This function terminates encryption
 * @link https://php.net/manual/en/function.mcrypt-generic-end.php
 * @param resource $td
 * @return bool
 * @removed 7.0
 */
#[Deprecated(since: '5.4')]
function mcrypt_generic_end ($td) {}

/**
 * This function deinitializes an encryption module
 * @link https://php.net/manual/en/function.mcrypt-generic-deinit.php
 * @param resource $td <p>
 * The encryption descriptor.
 * </p>
 * @return bool true on success or false on failure.
 */
#[Deprecated(since: '7.1')]
function mcrypt_generic_deinit ($td) {}

/**
 * Runs a self test on the opened module
 * @link https://php.net/manual/en/function.mcrypt-enc-self-test.php
 * @param resource $td <p>
 * The encryption descriptor.
 * </p>
 * @return int Returns 0 on success and a negative integer on failure
 */
#[Deprecated(since: '7.1')]
function mcrypt_enc_self_test ($td) {}

/**
 * Checks whether the encryption of the opened mode works on blocks
 * @link https://php.net/manual/en/function.mcrypt-enc-is-block-algorithm-mode.php
 * @param resource $td <p>
 * The encryption descriptor.
 * </p>
 * @return bool true if the mode is for use with block algorithms, otherwise it
 * returns false.
 */
#[Deprecated(since: '7.1')]
function mcrypt_enc_is_block_algorithm_mode ($td) {}

/**
 * Checks whether the algorithm of the opened mode is a block algorithm
 * @link https://php.net/manual/en/function.mcrypt-enc-is-block-algorithm.php
 * @param resource $td <p>
 * The encryption descriptor.
 * </p>
 * @return bool true if the algorithm is a block algorithm or false if it is
 * a stream one.
 */
#[Deprecated(since: '7.1')]
function mcrypt_enc_is_block_algorithm ($td) {}

/**
 * Checks whether the opened mode outputs blocks
 * @link https://php.net/manual/en/function.mcrypt-enc-is-block-mode.php
 * @param resource $td <p>
 * The encryption descriptor.
 * </p>
 * @return bool true if the mode outputs blocks of bytes or false if it outputs bytes.
 */
#[Deprecated(since: '7.1')]
function mcrypt_enc_is_block_mode ($td) {}

/**
 * Returns the blocksize of the opened algorithm
 * @link https://php.net/manual/en/function.mcrypt-enc-get-block-size.php
 * @param resource $td <p>
 * The encryption descriptor.
 * </p>
 * @return int the block size of the specified algorithm in bytes.
 */
#[Deprecated(since: '7.1')]
function mcrypt_enc_get_block_size ($td) {}

/**
 * Returns the maximum supported keysize of the opened mode
 * @link https://php.net/manual/en/function.mcrypt-enc-get-key-size.php
 * @param resource $td <p>
 * The encryption descriptor.
 * </p>
 * @return int the maximum supported key size of the algorithm in bytes.
 */
#[Deprecated(since: '7.1')]
function mcrypt_enc_get_key_size ($td) {}

/**
 * Returns an array with the supported keysizes of the opened algorithm
 * @link https://php.net/manual/en/function.mcrypt-enc-get-supported-key-sizes.php
 * @param resource $td <p>
 * The encryption descriptor.
 * </p>
 * @return array an array with the key sizes supported by the algorithm
 * specified by the encryption descriptor. If it returns an empty
 * array then all key sizes between 1 and
 * mcrypt_enc_get_key_size are supported by the
 * algorithm.
 */
#[Deprecated(since: '7.1')]
function mcrypt_enc_get_supported_key_sizes ($td) {}

/**
 * Returns the size of the IV of the opened algorithm
 * @link https://php.net/manual/en/function.mcrypt-enc-get-iv-size.php
 * @param resource $td <p>
 * The encryption descriptor.
 * </p>
 * @return int the size of the IV, or 0 if the IV is ignored in the algorithm.
 */
#[Deprecated(since: '7.1')]
function mcrypt_enc_get_iv_size ($td) {}

/**
 * Returns the name of the opened algorithm
 * @link https://php.net/manual/en/function.mcrypt-enc-get-algorithms-name.php
 * @param resource $td <p>
 * The encryption descriptor.
 * </p>
 * @return string the name of the opened algorithm as a string.
 */
#[Deprecated(since: '7.1')]
function mcrypt_enc_get_algorithms_name ($td) {}

/**
 * Returns the name of the opened mode
 * @link https://php.net/manual/en/function.mcrypt-enc-get-modes-name.php
 * @param resource $td <p>
 * The encryption descriptor.
 * </p>
 * @return string the name as a string.
 */
#[Deprecated(since: '7.1')]
function mcrypt_enc_get_modes_name ($td) {}

/**
 * This function runs a self test on the specified module
 * @link https://php.net/manual/en/function.mcrypt-module-self-test.php
 * @param string $algorithm <p>
 * One of the <b>MCRYPT_ciphername</b> constants, or the name of the algorithm as string.
 * </p>
 * @param string $lib_dir [optional] <p>
 * The optional lib_dir parameter can contain the
 * location of where the algorithm module is on the system.
 * </p>
 * @return bool The function returns true if the self test succeeds, or false when if
 * fails.
 */
#[Deprecated(since: '7.1')]
function mcrypt_module_self_test ($algorithm, $lib_dir = null) {}

/**
 * Returns if the specified module is a block algorithm or not
 * @link https://php.net/manual/en/function.mcrypt-module-is-block-algorithm-mode.php
 * @param string $mode <p>
 * The mode to check.
 * </p>
 * @param string $lib_dir [optional] <p>
 * The optional lib_dir parameter can contain the
 * location of where the algorithm module is on the system.
 * </p>
 * @return bool This function returns true if the mode is for use with block
 * algorithms, otherwise it returns false. (e.g. false for stream, and
 * true for cbc, cfb, ofb).
 */
#[Deprecated(since: '7.1')]
function mcrypt_module_is_block_algorithm_mode ($mode, $lib_dir = null) {}

/**
 * This function checks whether the specified algorithm is a block algorithm
 * @link https://php.net/manual/en/function.mcrypt-module-is-block-algorithm.php
 * @param string $algorithm <p>
 * The algorithm to check.
 * </p>
 * @param string $lib_dir [optional] <p>
 * The optional lib_dir parameter can contain the
 * location of where the algorithm module is on the system.
 * </p>
 * @return bool This function returns true if the specified algorithm is a block
 * algorithm, or false is it is a stream algorithm.
 */
#[Deprecated(since: '7.1')]
function mcrypt_module_is_block_algorithm ($algorithm, $lib_dir = null) {}

/**
 * Returns if the specified mode outputs blocks or not
 * @link https://php.net/manual/en/function.mcrypt-module-is-block-mode.php
 * @param string $mode <p>
 * The mode to check.
 * </p>
 * @param string $lib_dir [optional] <p>
 * The optional lib_dir parameter can contain the
 * location of where the algorithm module is on the system.
 * </p>
 * @return bool This function returns true if the mode outputs blocks of bytes or
 * false if it outputs just bytes. (e.g. true for cbc and ecb, and
 * false for cfb and stream).
 */
#[Deprecated(since: '7.1')]
function mcrypt_module_is_block_mode ($mode, $lib_dir = null) {}

/**
 * Returns the blocksize of the specified algorithm
 * @link https://php.net/manual/en/function.mcrypt-module-get-algo-block-size.php
 * @param string $algorithm <p>
 * The algorithm name.
 * </p>
 * @param string $lib_dir [optional] <p>
 * This optional parameter can contain the location where the mode module
 * is on the system.
 * </p>
 * @return int the block size of the algorithm specified in bytes.
 */
#[Deprecated(since: '7.1')]
function mcrypt_module_get_algo_block_size ($algorithm, $lib_dir = null) {}

/**
 * Returns the maximum supported keysize of the opened mode
 * @link https://php.net/manual/en/function.mcrypt-module-get-algo-key-size.php
 * @param string $algorithm <p>
 * The algorithm name.
 * </p>
 * @param string $lib_dir [optional] <p>
 * This optional parameter can contain the location where the mode module
 * is on the system.
 * </p>
 * @return int This function returns the maximum supported key size of the
 * algorithm specified in bytes.
 */
#[Deprecated(since: '7.1')]
function mcrypt_module_get_algo_key_size ($algorithm, $lib_dir = null) {}

/**
 * Returns an array with the supported keysizes of the opened algorithm
 * @link https://php.net/manual/en/function.mcrypt-module-get-supported-key-sizes.php
 * @param string $algorithm <p>
 * The algorithm to used.
 * </p>
 * @param string $lib_dir [optional] <p>
 * The optional lib_dir parameter can contain the
 * location of where the algorithm module is on the system.
 * </p>
 * @return array an array with the key sizes supported by the specified algorithm.
 * If it returns an empty array then all key sizes between 1 and
 * mcrypt_module_get_algo_key_size are supported by the
 * algorithm.
 */
#[Deprecated(since: '7.1')]
function mcrypt_module_get_supported_key_sizes ($algorithm, $lib_dir = null) {}

/**
 * Closes the mcrypt module
 * @link https://php.net/manual/en/function.mcrypt-module-close.php
 * @param resource $td <p>
 * The encryption descriptor.
 * </p>
 * @return bool true on success or false on failure.
 */
#[Deprecated(since: '7.1')]
function mcrypt_module_close ($td) {}

define ('MCRYPT_ENCRYPT', 0);
define ('MCRYPT_DECRYPT', 1);
define ('MCRYPT_DEV_RANDOM', 0);
define ('MCRYPT_DEV_URANDOM', 1);
define ('MCRYPT_RAND', 2);
define ('MCRYPT_3DES', "tripledes");
define ('MCRYPT_ARCFOUR_IV', "arcfour-iv");
define ('MCRYPT_ARCFOUR', "arcfour");
define ('MCRYPT_BLOWFISH', "blowfish");
define ('MCRYPT_BLOWFISH_COMPAT', "blowfish-compat");
define ('MCRYPT_CAST_128', "cast-128");
define ('MCRYPT_CAST_256', "cast-256");
define ('MCRYPT_CRYPT', "crypt");
define ('MCRYPT_DES', "des");
define ('MCRYPT_ENIGNA', "crypt");
define ('MCRYPT_GOST', "gost");
define ('MCRYPT_LOKI97', "loki97");
define ('MCRYPT_PANAMA', "panama");
define ('MCRYPT_RC2', "rc2");
define ('MCRYPT_RIJNDAEL_128', "rijndael-128");
define ('MCRYPT_RIJNDAEL_192', "rijndael-192");
define ('MCRYPT_RIJNDAEL_256', "rijndael-256");
define ('MCRYPT_SAFER64', "safer-sk64");
define ('MCRYPT_SAFER128', "safer-sk128");
define ('MCRYPT_SAFERPLUS', "saferplus");
define ('MCRYPT_SERPENT', "serpent");
define ('MCRYPT_THREEWAY', "threeway");
define ('MCRYPT_TRIPLEDES', "tripledes");
define ('MCRYPT_TWOFISH', "twofish");
define ('MCRYPT_WAKE', "wake");
define ('MCRYPT_XTEA', "xtea");
define ('MCRYPT_IDEA', "idea");
define ('MCRYPT_MARS', "mars");
define ('MCRYPT_RC6', "rc6");
define ('MCRYPT_SKIPJACK', "skipjack");
define ('MCRYPT_MODE_CBC', "cbc");
define ('MCRYPT_MODE_CFB', "cfb");
define ('MCRYPT_MODE_ECB', "ecb");
define ('MCRYPT_MODE_NOFB', "nofb");
define ('MCRYPT_MODE_OFB', "ofb");
define ('MCRYPT_MODE_STREAM', "stream");

// End of mcrypt v.
?>
