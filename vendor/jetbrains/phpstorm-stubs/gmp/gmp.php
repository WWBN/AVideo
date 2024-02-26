<?php

// Start of gmp v.
use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\Pure;

/**
 * Create GMP number
 * @link https://php.net/manual/en/function.gmp-init.php
 * @param mixed $number <p>
 * An integer or a string. The string representation can be decimal,
 * hexadecimal or octal.
 * </p>
 * @param int $base [optional] <p>
 * The base.
 * </p>
 * <p>
 * The base may vary from 2 to 36. If base is 0 (default value), the
 * actual base is determined from the leading characters: if the first
 * two characters are 0x or 0X,
 * hexadecimal is assumed, otherwise if the first character is "0",
 * octal is assumed, otherwise decimal is assumed.
 * </p>
 * @return resource|GMP A GMP number resource.
 */
#[Pure]
function gmp_init ($number, $base = 0) {}

/**
 * Convert GMP number to integer
 * @link https://php.net/manual/en/function.gmp-intval.php
 * @param resource|string|GMP $gmpnumber <p>
 * A GMP number.
 * </p>
 * @return int An integer value of <i>gmpnumber</i>.
 */
#[Pure]
function gmp_intval ($gmpnumber) {}

/**
 * Sets the RNG seed
 * @param resource|string|GMP $seed <p>
 * The seed to be set for the {@see gmp_random()}, {@see gmp_random_bits()}, and {@see gmp_random_range()} functions.
 * </p>
 * Either a GMP number resource in PHP 5.5 and earlier, a GMP object in PHP 5.6 and later, or a numeric string provided that it is possible to convert the latter to a number.
 * @return null|false Returns NULL on success.
 * @since 7.0
 */
function gmp_random_seed ($seed ) {}
/**
 * Convert GMP number to string
 * @link https://php.net/manual/en/function.gmp-strval.php
 * @param resource|string|GMP $gmpnumber <p>
 * The GMP number that will be converted to a string.
 * </p>
 * It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @param int $base [optional] <p>
 * The base of the returned number. The default base is 10.
 * Allowed values for the base are from 2 to 62 and -2 to -36.
 * </p>
 * @return string The number, as a string.
 */
#[Pure]
function gmp_strval ($gmpnumber, $base = 10) {}

/**
 * Add numbers
 * @link https://php.net/manual/en/function.gmp-add.php
 * @param resource|string|GMP $a <p>
 * A number that will be added.
 * </p>
 * It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @param resource|string|GMP $b <p>
 * A number that will be added.
 * </p>
 * It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @return resource|GMP A GMP number representing the sum of the arguments.
 */
#[Pure]
function gmp_add ($a, $b) {}

/**
 * Subtract numbers
 * @link https://php.net/manual/en/function.gmp-sub.php
 * @param resource|string|GMP $a <p>
 * The number being subtracted from.
 * </p>
 * It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @param resource|string|GMP $b <p>
 * The number subtracted from <i>a</i>.
 * </p>
 * It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @return resource|GMP A GMP number resource.
 */
#[Pure]
function gmp_sub ($a, $b) {}

/**
 * Multiply numbers
 * @link https://php.net/manual/en/function.gmp-mul.php
 * @param resource|string|GMP $a <p>
 * A number that will be multiplied by <i>b</i>.
 * </p>
 * It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @param resource|string|GMP $b <p>
 * A number that will be multiplied by <i>a</i>.
 * </p>
 * It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @return resource|GMP A GMP number resource.
 */
#[Pure]
function gmp_mul ($a, $b) {}

/**
 * Divide numbers and get quotient and remainder
 * @link https://php.net/manual/en/function.gmp-div-qr.php
 * @param resource|string|GMP $n <p>
 * The number being divided.
 * </p>
 * It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @param resource|string|GMP $d <p>
 * The number that <i>n</i> is being divided by.
 * </p>
 * It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @param int $round [optional] <p>
 * See the <b>gmp_div_q</b> function for description
 * of the <i>round</i> argument.
 * </p>
 * @return array an array, with the first
 * element being [n/d] (the integer result of the
 * division) and the second being (n - [n/d] * d)
 * (the remainder of the division).
 */
#[Pure]
function gmp_div_qr ($n, $d, $round = GMP_ROUND_ZERO) {}

/**
 * Divide numbers
 * @link https://php.net/manual/en/function.gmp-div-q.php
 * @param resource|string|GMP $a <p>
 * The number being divided.
 * </p>
 * It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @param resource|string|GMP $b <p>
 * The number that <i>a</i> is being divided by.
 * </p>
 * It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @param int $round [optional] <p>
 * The result rounding is defined by the
 * <i>round</i>, which can have the following
 * values:
 * <b>GMP_ROUND_ZERO</b>: The result is truncated
 * towards 0.
 * @return resource|GMP A GMP number resource.
 */
#[Pure]
function gmp_div_q ($a, $b, $round = GMP_ROUND_ZERO) {}

/**
 * Remainder of the division of numbers
 * @link https://php.net/manual/en/function.gmp-div-r.php
 * @param resource|string|GMP $n <p>
 * The number being divided.
 * </p>
 * It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @param resource|string|GMP $d <p>
 * The number that <i>n</i> is being divided by.
 * </p>
 * It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @param int $round [optional] <p>
 * See the <b>gmp_div_q</b> function for description
 * of the <i>round</i> argument.
 * </p>
 * @return resource|GMP The remainder, as a GMP number.
 */
#[Pure]
function gmp_div_r ($n, $d, $round = GMP_ROUND_ZERO) {}

/**
 * Divide numbers
 * @link https://php.net/manual/en/function.gmp-div-q.php
 * @param resource|string|GMP $a <p>
 * The number being divided.
 * </p>
 * It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @param resource|string|GMP $b <p>
 * The number that <i>a</i> is being divided by.
 * </p>
 * It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @param int $round [optional] <p>
 * The result rounding is defined by the
 * <i>round</i>, which can have the following
 * values:
 * <b>GMP_ROUND_ZERO</b>: The result is truncated
 * towards 0.
 * @return resource|GMP A GMP number resource.
 */
#[Pure]
function gmp_div ($a, $b, $round = GMP_ROUND_ZERO) {}

/**
 * Modulo operation
 * @link https://php.net/manual/en/function.gmp-mod.php
 * @param resource|string|GMP $n It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @param resource|string|GMP $d <p>
 * The modulo that is being evaluated.
 * </p>
 * It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @return resource|GMP A GMP number resource.
 */
#[Pure]
function gmp_mod ($n, $d) {}

/**
 * Exact division of numbers
 * @link https://php.net/manual/en/function.gmp-divexact.php
 * @param resource|string|GMP $n <p>
 * The number being divided.
 * </p>
 * It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @param resource|string|GMP $d <p>
 * The number that <i>a</i> is being divided by.
 * </p>
 * It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @return resource|GMP A GMP number resource.
 */
#[Pure]
function gmp_divexact ($n, $d) {}

/**
 * Negate number
 * @link https://php.net/manual/en/function.gmp-neg.php
 * @param resource|string|GMP $a It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @return resource|GMP -<i>a</i>, as a GMP number.
 */
#[Pure]
function gmp_neg ($a) {}

/**
 * Absolute value
 * @link https://php.net/manual/en/function.gmp-abs.php
 * @param resource|string|GMP $a It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @return resource|GMP the absolute value of <i>a</i>, as a GMP number.
 */
#[Pure]
function gmp_abs ($a) {}

/**
 * Factorial
 * @link https://php.net/manual/en/function.gmp-fact.php
 * @param resource|string|GMP $a <p>
 * The factorial number.
 * </p>
 * It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @return resource|GMP A GMP number resource.
 */
#[Pure]
function gmp_fact ($a) {}

/**
 * Calculate square root
 * @link https://php.net/manual/en/function.gmp-sqrt.php
 * @param resource|string|GMP $a It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @return resource|GMP The integer portion of the square root, as a GMP number.
 */
#[Pure]
function gmp_sqrt ($a) {}

/**
 * Square root with remainder
 * @link https://php.net/manual/en/function.gmp-sqrtrem.php
 * @param resource|string|GMP $a <p>
 * The number being square rooted.
 * </p>
 * It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @return array array where first element is the integer square root of
 * <i>a</i> and the second is the remainder
 * (i.e., the difference between <i>a</i> and the
 * first element squared).
 */
#[Pure]
function gmp_sqrtrem ($a) {}

/**
 * Raise number into power
 * @link https://php.net/manual/en/function.gmp-pow.php
 * @param resource|string|GMP $base <p>
 * The base number.
 * </p>
 * It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @param int $exp <p>
 * The positive power to raise the <i>base</i>.
 * </p>
 * @return resource|GMP The new (raised) number, as a GMP number. The case of
 * 0^0 yields 1.
 */
#[Pure]
function gmp_pow ($base, $exp) {}

/**
 * Raise number into power with modulo
 * @link https://php.net/manual/en/function.gmp-powm.php
 * @param resource|string|GMP $base <p>
 * The base number.
 * </p>
 * It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @param resource|string|GMP $exp <p>
 * The positive power to raise the <i>base</i>.
 * </p>
 * It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @param resource|string|GMP $mod <p>
 * The modulo.
 * </p>
 * It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @return resource|GMP The new (raised) number, as a GMP number.
 */
#[Pure]
function gmp_powm ($base, $exp, $mod) {}

/**
 * Perfect square check
 * @link https://php.net/manual/en/function.gmp-perfect-square.php
 * @param resource|string|GMP $a <p>
 * The number being checked as a perfect square.
 * </p>
 * It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @return bool <b>TRUE</b> if <i>a</i> is a perfect square,
 * <b>FALSE</b> otherwise.
 */
#[Pure]
function gmp_perfect_square ($a) {}

/**
 * Check if number is "probably prime"
 * @link https://php.net/manual/en/function.gmp-prob-prime.php
 * @param resource|string|GMP $a <p>
 * The number being checked as a prime.
 * </p>
 * It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @param int $reps [optional] <p>
 * Reasonable values
 * of <i>reps</i> vary from 5 to 10 (default being
 * 10); a higher value lowers the probability for a non-prime to
 * pass as a "probable" prime.
 * </p>
 * It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @return int If this function returns 0, <i>a</i> is
 * definitely not prime. If it returns 1, then
 * <i>a</i> is "probably" prime. If it returns 2,
 * then <i>a</i> is surely prime.
 */
#[Pure]
function gmp_prob_prime ($a, $reps = 10) {}

/**
 * Random number
 * @link https://php.net/manual/en/function.gmp-random-bits.php
 * @param int $bits <p>The number of bits. Either a GMP number resource in PHP 5.5 and earlier,
 * a GMP object in PHP 5.6 and later,
 * or a numeric string provided that it is possible to convert the latter to a number.</p>
 * @return GMP A random GMP number.
 */
#[Pure]
function gmp_random_bits($bits) {}

/**
 * Random number
 * @link https://php.net/manual/en/function.gmp-random-range.php
 * @param GMP $min <p>A GMP number representing the lower bound for the random number</p>
 * @param GMP $max <p>A GMP number representing the upper bound for the random number</p>
 * @return GMP A random GMP number.
 */
#[Pure]
function gmp_random_range(GMP $min, GMP $max) {}

/**
 * Calculate GCD
 * @link https://php.net/manual/en/function.gmp-gcd.php
 * @param resource|string|GMP $a It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @param resource|string|GMP $b It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @return resource|GMP A positive GMP number that divides into both
 * <i>a</i> and <i>b</i>.
 */
#[Pure]
function gmp_gcd ($a, $b) {}

/**
 * Calculate GCD and multipliers
 * @link https://php.net/manual/en/function.gmp-gcdext.php
 * @param resource|string|GMP $a It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @param resource|string|GMP $b It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @return array An array of GMP numbers.
 */
#[Pure]
function gmp_gcdext ($a, $b) {}

/**
 * Inverse by modulo
 * @link https://php.net/manual/en/function.gmp-invert.php
 * @param resource|string|GMP $a It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @param resource|string|GMP $b It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @return resource|GMP|false A GMP number on success or <b>FALSE</b> if an inverse does not exist.
 */
#[Pure]
function gmp_invert ($a, $b) {}

/**
 * Jacobi symbol
 * @link https://php.net/manual/en/function.gmp-jacobi.php
 * @param resource|string|GMP $a It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @param resource|string|GMP $p It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * <p>
 * Should be odd and must be positive.
 * </p>
 * @return int A GMP number resource.
 */
#[Pure]
function gmp_jacobi ($a, $p) {}

/**
 * Legendre symbol
 * @link https://php.net/manual/en/function.gmp-legendre.php
 * @param resource|string|GMP $a It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @param resource|string|GMP $p It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * <p>
 * Should be odd and must be positive.
 * </p>
 * @return int A GMP number resource.
 */
#[Pure]
function gmp_legendre ($a, $p) {}

/**
 * Compare numbers
 * @link https://php.net/manual/en/function.gmp-cmp.php
 * @param resource|string|GMP $a It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @param resource|string|GMP $b It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @return int a positive value if a &gt; b, zero if
 * a = b and a negative value if a &lt;
 * b.
 */
#[Pure]
function gmp_cmp ($a, $b) {}

/**
 * Sign of number
 * @link https://php.net/manual/en/function.gmp-sign.php
 * @param resource|string|GMP $a It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @return int 1 if <i>a</i> is positive,
 * -1 if <i>a</i> is negative,
 * and 0 if <i>a</i> is zero.
 */
#[Pure]
function gmp_sign ($a) {}

/**
 * Random number
 * @link https://php.net/manual/en/function.gmp-random.php
 * @param int $limiter [optional] <p>
 * The limiter.
 * </p>
 * It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @return resource|GMP A random GMP number.
 * @see gmp_random_bits()
 * @see gmp_random_range()
 * @removed 8.0
 */

#[Deprecated(reason: "Use see gmp_random_bits() or see gmp_random_range() instead", since: "7.2")]
#[Pure]
function gmp_random ($limiter = 20) {}

/**
 * Bitwise AND
 * @link https://php.net/manual/en/function.gmp-and.php
 * @param resource|string|GMP $a It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @param resource|string|GMP $b It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @return resource|GMP A GMP number representing the bitwise AND comparison.
 */
#[Pure]
function gmp_and ($a, $b) {}

/**
 * Bitwise OR
 * @link https://php.net/manual/en/function.gmp-or.php
 * @param resource|string|GMP $a It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @param resource|string|GMP $b It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @return resource|GMP A GMP number resource.
 */
#[Pure]
function gmp_or ($a, $b) {}

/**
 * Calculates one's complement
 * @link https://php.net/manual/en/function.gmp-com.php
 * @param resource|string|GMP $a It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @return resource|GMP the one's complement of <i>a</i>, as a GMP number.
 */
#[Pure]
function gmp_com ($a) {}

/**
 * Bitwise XOR
 * @link https://php.net/manual/en/function.gmp-xor.php
 * @param resource|string|GMP $a It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @param resource|string|GMP $b It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @return resource|GMP A GMP number resource.
 */
#[Pure]
function gmp_xor ($a, $b) {}

/**
 * Set bit
 * @link https://php.net/manual/en/function.gmp-setbit.php
 * @param resource|string|GMP &$a <p>
 * The number being set to.
 * </p>
 * It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @param int $index <p>
 * The set bit.
 * </p>
 * @param bool $set_clear [optional] <p>
 * Defines if the bit is set to 0 or 1. By default the bit is set to
 * 1. Index starts at 0.
 * </p>
 * @return void A GMP number resource.
 */
function gmp_setbit (&$a, $index, $set_clear = true) {}

/**
 * Clear bit
 * @link https://php.net/manual/en/function.gmp-clrbit.php
 * @param resource|string|GMP &$a It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @param int $index It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @return void A GMP number resource.
 */
function gmp_clrbit (&$a, $index) {}

/**
 * Scan for 0
 * @link https://php.net/manual/en/function.gmp-scan0.php
 * @param resource|string|GMP $a <p>
 * The number to scan.
 * </p>
 * It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @param int $start <p>
 * The starting bit.
 * </p>
 * @return int the index of the found bit, as an integer. The
 * index starts from 0.
 */
#[Pure]
function gmp_scan0 ($a, $start) {}

/**
 * Scan for 1
 * @link https://php.net/manual/en/function.gmp-scan1.php
 * @param resource|string|GMP $a <p>
 * The number to scan.
 * </p>
 * It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @param int $start <p>
 * The starting bit.
 * </p>
 * @return int the index of the found bit, as an integer.
 * If no set bit is found, -1 is returned.
 */
#[Pure]
function gmp_scan1 ($a, $start) {}

/**
 * Tests if a bit is set
 * @link https://php.net/manual/en/function.gmp-testbit.php
 * @param resource|string|GMP $a It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @param int $index <p>
 * The bit to test
 * </p>
 * @return bool <b>TRUE</b> on success or <b>FALSE</b> on failure.
 */
#[Pure]
function gmp_testbit ($a, $index) {}

/**
 * Population count
 * @link https://php.net/manual/en/function.gmp-popcount.php
 * @param resource|string|GMP $a It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @return int The population count of <i>a</i>, as an integer.
 */
#[Pure]
function gmp_popcount ($a) {}

/**
 * Hamming distance
 * @link https://php.net/manual/en/function.gmp-hamdist.php
 * @param resource|string|GMP $a It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * <p>
 * It should be positive.
 * </p>
 * @param resource|string|GMP $b It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * <p>
 * It should be positive.
 * </p>
 * @return int A GMP number resource.
 */
#[Pure]
function gmp_hamdist ($a, $b) {}

/**
 * Import from a binary string
 * @link https://php.net/manual/en/function.gmp-import.php
 * @param string $data The binary string being imported
 * @param integer $word_size Default value is 1. The number of bytes in each chunk of binary
 * data. This is mainly used in conjunction with the options parameter.</p>
 * @param integer $options Default value is GMP_MSW_FIRST | GMP_NATIVE_ENDIAN.
 * @return GMP|false Returns a GMP number or FALSE on failure.
 * @since 5.6.1
 */
#[Pure]
function gmp_import ($data, $word_size = 1, $options = GMP_MSW_FIRST | GMP_NATIVE_ENDIAN) {}

/**
 * Export to a binary string
 * @link https://php.net/manual/en/function.gmp-export.php
 * @param GMP $gmpnumber The GMP number being exported
 * @param integer $word_size Default value is 1. The number of bytes in each chunk of binary
 * data. This is mainly used in conjunction with the options parameter.</p>
 * @param integer $options Default value is GMP_MSW_FIRST | GMP_NATIVE_ENDIAN.
 * @return string|false Returns a string or FALSE on failure.
 * @since 5.6.1
 */
#[Pure]
function gmp_export (GMP $gmpnumber, $word_size = 1, $options = GMP_MSW_FIRST | GMP_NATIVE_ENDIAN) {}

/**
 * Takes the nth root of a and returns the integer component of the result.
 * @link https://php.net/manual/en/function.gmp-root.php
 * @param GMP $a Either a GMP number resource in PHP 5.5 and earlier, a GMP object in PHP 5.6
 * and later, or a numeric string provided that it is possible to convert the latter to a number.</p>
 * @param integer $nth The positive root to take of a.
 * @return GMP The integer component of the resultant root, as a GMP number.
 * @since 5.6
 */
#[Pure]
function gmp_root (GMP $a, $nth) {}

/**
 * Takes the nth root of a and returns the integer component and remainder of the result.
 * @link https://php.net/manual/en/function.gmp-rootrem.php
 * @param GMP $a Either a GMP number resource in PHP 5.5 and earlier, a GMP object in PHP 5.6
 * and later, or a numeric string provided that it is possible to convert the latter to a number.</p>
 * @param integer $nth The positive root to take of a.
 * @return array|GMP[] A two element array, where the first element is the integer component of
 * the root, and the second element is the remainder, both represented as GMP numbers.</p>
 * @since 5.6
 */
#[Pure]
function gmp_rootrem (GMP $a, $nth) {}

/**
 * Find next prime number
 * @link https://php.net/manual/en/function.gmp-nextprime.php
 * @param int|resource|string|GMP $a It can be either a GMP number resource, or a
 * numeric string given that it is possible to convert the latter to a number.</p>
 * @return resource Return the next prime number greater than <i>a</i>,
 * as a GMP number.
 */
#[Pure]
function gmp_nextprime ($a) {}

/**
 * Calculates binomial coefficient
 *
 * @link https://www.php.net/manual/en/function.gmp-binomial.php
 *
 * @param GMP|string|float|int $a
 * @param int $b
 * @return GMP|false
 *
 * @since 7.3
 */
#[Pure]
function gmp_binomial($a, $b) {}

/**
 * Computes the Kronecker symbol
 *
 * @link https://www.php.net/manual/en/function.gmp-kronecker.php
 *
 * @param GMP|string|float|int $a
 * @param GMP|string|float|int $b
 * @return int
 *
 * @since 7.3
 */
#[Pure]
function gmp_kronecker($a, $b) {}

/**
 * Computes the least common multiple of A and B
 *
 * @link https://www.php.net/manual/en/function.gmp-lcm.php
 *
 * @param GMP|string|float|int $a
 * @param GMP|string|float|int $b
 * @return GMP
 *
 * @since 7.3
 */
#[Pure]
function gmp_lcm($a, $b) {}

/**
 * Perfect power check
 *
 * @link https://www.php.net/manual/en/function.gmp-perfect-power.php
 *
 * @param GMP|string|float|int $a
 * @return bool
 *
 * @since 7.3
 */
#[Pure]
function gmp_perfect_power($a) {}

define ('GMP_ROUND_ZERO', 0);
define ('GMP_ROUND_PLUSINF', 1);
define ('GMP_ROUND_MINUSINF', 2);
define ('GMP_MSW_FIRST', 1);
define ('GMP_LSW_FIRST', 2);
define ('GMP_LITTLE_ENDIAN', 4);
define ('GMP_BIG_ENDIAN', 8);
define ('GMP_NATIVE_ENDIAN', 16);

/**
 * The GMP library version
 * @link https://php.net/manual/en/gmp.constants.php
 */
define ('GMP_VERSION', "");

define ('GMP_MPIR_VERSION', '3.0.0');

class GMP implements Serializable {

    /**
     * String representation of object
     * @link https://php.net/manual/en/serializable.serialize.php
     * @return string the string representation of the object or null
     */
    public function serialize() {}

    /**
     * Constructs the object
     * @link https://php.net/manual/en/serializable.unserialize.php
     * @param string $serialized <p>
     * The string representation of the object.
     * </p>
     * @return void
     */
    public function unserialize($serialized) {}
}
// End of gmp v.
?>
