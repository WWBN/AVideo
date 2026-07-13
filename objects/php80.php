<?php
/**
 * This file: Some polyfills for PHP8 to cover some functions used by AVideo,
 * not introduced to PHP until PHP8 (the minimum PHP version required for
 * AVideo, according to its composer.json file, is PHP7.3, so to ensure that
 * usage of these functions in the codebase isn't a BC-break, polyfills are
 * necessary.
 */

if (\PHP_VERSION_ID < 80000) {
    if (!function_exists('str_contains')) {
        function str_contains(string $Haystack, string $Needle): bool
        {
            return strpos($Haystack, $Needle) !== false;
        }
    }
    if (!function_exists('str_starts_with')) {
        function str_starts_with(string $Haystack, string $Needle): bool
        {
            return substr($Haystack, 0, strlen($Needle)) === $Needle;
        }
    }
    if (!function_exists('str_ends_with')) {
        function str_ends_with(string $Haystack, string $Needle): bool
        {
            return substr($Haystack, strlen($Needle) * -1) === $Needle;
        }
    }
}
