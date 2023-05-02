<?php
/**
 * Generates cryptographically secure pseudo-random bytes
 * @link https://php.net/manual/en/function.random-bytes.php
 * @param int $length The length of the random string that should be returned in bytes.
 * @return string Returns a string containing the requested number of cryptographically secure random bytes.
 * @since 7.0
 * @throws Exception if it was not possible to gather sufficient entropy.
 */
function random_bytes (int $length): string
{}

/**
 * Generates cryptographically secure pseudo-random integers
 * @link https://php.net/manual/en/function.random-int.php
 * @param int $min The lowest value to be returned, which must be PHP_INT_MIN or higher.
 * @param int $max The highest value to be returned, which must be less than or equal to PHP_INT_MAX.
 * @return int Returns a cryptographically secure random integer in the range min to max, inclusive.
 * @since 7.0
 * @throws Exception if it was not possible to gather sufficient entropy.
 */
function random_int (int $min, int $max): int
{}
