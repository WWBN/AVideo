<?php
/** GNUPG Constants
 * @link https://php.net/manual/en/gnupg.constants.php
 */
define('GNUPG_SIG_MODE_NORMAL', 0);
define('GNUPG_SIG_MODE_DETACH', 1);
define('GNUPG_SIG_MODE_CLEAR', 2);
define('GNUPG_VALIDITY_UNKNOWN', 0);
define('GNUPG_VALIDITY_UNDEFINED', 1);
define('GNUPG_VALIDITY_NEVER', 2);
define('GNUPG_VALIDITY_MARGINAL', 3);
define('GNUPG_VALIDITY_FULL', 4);
define('GNUPG_VALIDITY_ULTIMATE', 5);
define('GNUPG_PROTOCOL_OpenPGP', 0);
define('GNUPG_PROTOCOL_CMS', 1);
define('GNUPG_SIGSUM_VALID', 1);
define('GNUPG_SIGSUM_GREEN', 2);
define('GNUPG_SIGSUM_RED', 4);
define('GNUPG_SIGSUM_KEY_REVOKED', 16);
define('GNUPG_SIGSUM_KEY_EXPIRED', 32);
define('GNUPG_SIGSUM_SIG_EXPIRED', 64);
define('GNUPG_SIGSUM_KEY_MISSING', 128);
define('GNUPG_SIGSUM_CRL_MISSING', 256);
define('GNUPG_SIGSUM_CRL_TOO_OLD', 512);
define('GNUPG_SIGSUM_BAD_POLICY', 1024);
define('GNUPG_SIGSUM_SYS_ERROR', 2048);
define('GNUPG_ERROR_WARNING', 1);
define('GNUPG_ERROR_EXCEPTION', 2);
define('GNUPG_ERROR_SILENT', 3);

/**
 * GNUPG Encryption Class
 * @link https://php.net/manual/en/book.gnupg.php
 * Class gnupg
 */

class gnupg {
	/**
	 * Add a key for decryption
	 * @link https://php.net/manual/en/function.gnupg-adddecryptkey.php
	 *
	 * @param string $fingerprint
	 * @param string $passphrase
	 *
	 * @return bool
	 */
	function adddecryptkey($fingerprint, $passphrase)
	{
	}

	/**
	 * Verifies a signed text
	 * @link https://php.net/manual/en/function.gnupg-verify.php
	 *
	 * * @param string $signed_text
	 * @param string $signature
	 * @param string &$plaintext
	 *
	 * @return array|false On success, this function returns information about the signature.
	 *               On failure, this function returns false.
	 */
	function verify($signed_text, $signature, &$plaintext = null)
	{
	}

	/**
	 * Add a key for encryption
	 * @link https://php.net/manual/en/function.gnupg-addencryptkey.php
	 *
	 * @param string $fingerprint
	 *
	 * @return bool
	 */
	function addencryptkey($fingerprint)
	{
	}

	/**
	 * Add a key for signing
	 * @link https://php.net/manual/en/function.gnupg-addsignkey.php
	 *
	 * @param string $fingerprint
	 * @param string $passphrase
	 *
	 * @return bool
	 */
	function addsignkey($fingerprint, $passphrase = null)
	{
	}

	/**
	 * Removes all keys which were set for decryption before
	 * @link https://php.net/manual/en/function.gnupg-cleardecryptkeys.php
	 *
	 * @return bool
	 */
	function cleardecryptkeys()
	{
	}

	/**
	 * Removes all keys which were set for encryption before
	 * @link https://php.net/manual/en/function.gnupg-clearencryptkeys.php
	 *
	 * @return bool
	 */
	function clearencryptkeys()
	{
	}

	/**
	 * Removes all keys which were set for signing before
	 * @link https://php.net/manual/en/function.gnupg-clearsignkeys.php
	 *
	 * @return bool
	 */
	function clearsignkeys()
	{
	}

	/**
	 * Decrypts a given text
	 * @link https://php.net/manual/en/function.gnupg-decrypt.php
	 *
	 * @param string $text
	 *
	 * @return string|false On success, this function returns the decrypted text.
	 *                On failure, this function returns false.
	 */
	function decrypt($text)
	{
	}

	/**
	 * Decrypts and verifies a given text
	 * @link https://php.net/manual/en/function.gnupg-decryptverify.php
	 *
	 * @param string $text
	 * @param string &$plaintext
	 *
	 * @return array|false On success, this function returns information about the signature and
	 *               fills the  parameter with the decrypted text.
	 *               On failure, this function returns false.
	 */
	function decryptverify($text, &$plaintext)
	{
	}

	/**
	 * Encrypts a given text
	 * @link https://php.net/manual/en/function.gnupg-encrypt.php
	 *
	 * @param string $plaintext
	 *
	 * @return string|false On success, this function returns the encrypted text.
	 *                On failure, this function returns false.
	 */
	function encrypt($plaintext)
	{
	}

	/**
	 * Encrypts and signs a given text
	 * @link https://php.net/manual/en/function.gnupg-encryptsign.php
	 *
	 * @param string $plaintext
	 *
	 * @return string|false On success, this function returns the encrypted and signed text.
	 *                On failure, this function returns false.
	 */
	function encryptsign($plaintext)
	{
	}

	/**
	 * Exports a key
	 * @link https://php.net/manual/en/function.gnupg-export.php
	 *
	 * @param string $fingerprint
	 *
	 * @return string|false On success, this function returns the keydata.
	 *                On failure, this function returns false.
	 */
	function export($fingerprint)
	{
	}

	/**
	 * Returns the errortext, if a function fails
	 * @link https://php.net/manual/en/function.gnupg-geterror.php
	 *
	 * @return string|false Returns an errortext, if an error has occurred, otherwise false.
	 */
	function geterror()
	{
	}

	/**
	 * Returns the currently active protocol for all operations
	 * @link https://php.net/manual/en/function.gnupg-getprotocol.php
	 *
	 * @return int Returns the currently active protocol, which can be one of
	 *             or
	 *             .
	 */
	function getprotocol()
	{
	}

	/**
	 * Imports a key
	 * @link https://php.net/manual/en/function.gnupg-import.php
	 *
	 * @param string $keydata
	 *
	 * @return array|false On success, this function returns and info-array about the importprocess.
	 *               On failure, this function returns false.
	 */
	function import($keydata)
	{
	}

	/**
	 * Initialize a connection
	 * @link https://php.net/manual/en/function.gnupg-init.php
	 *
	 * @return resource A GnuPG ``resource`` connection used by other GnuPG functions.
	 */
	function init()
	{
	}

	/**
	 * Returns an array with information about all keys that matches the given pattern
	 * @link https://php.net/manual/en/function.gnupg-keyinfo.php
	 *
	 * @param string $pattern
	 *
	 * @return array Returns an array with information about all keys that matches the given
	 *               pattern or false, if an error has occurred.
	 */
	function keyinfo($pattern)
	{
	}

	/**
	 * Toggle armored output
	 * @link https://php.net/manual/en/function.gnupg-setarmor.php
	 *
	 * @param int $armor
	 *
	 * @return bool
	 */
	function setarmor($armor)
	{
	}

	/**
	 * Sets the mode for error_reporting
	 * @link https://php.net/manual/en/function.gnupg-seterrormode.php
	 *
	 * @param int $errormode
	 *
	 * @return void
	 */
	function seterrormode($errormode)
	{
	}

	/**
	 * Sets the mode for signing
	 * @link https://php.net/manual/en/function.gnupg-setsignmode.php
	 *
	 * @param int $signmode
	 *
	 * @return bool
	 */
	function setsignmode($signmode)
	{
	}

	/**
	 * Signs a given text
	 * @link https://php.net/manual/en/function.gnupg-sign.php
	 *
	 * @param string $plaintext
	 *
	 * @return string|false On success, this function returns the signed text or the signature.
	 *                On failure, this function returns false.
	 */
	function sign($plaintext)
	{
	}

}
