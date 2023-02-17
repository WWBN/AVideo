<?php

// Start of tokenizer v.0.1
use JetBrains\PhpStorm\Pure;

/**
 * Split given source into PHP tokens
 * @link https://php.net/manual/en/function.token-get-all.php
 * @param string $code <p>
 * The PHP source to parse.
 * </p>
 * @param int $flags
 * <p>
 * <p>
 * Valid flags:
 * </p><ul>
 * <li>
 *
 * <b>TOKEN_PARSE</b> - Recognises the ability to use
 * reserved words in specific contexts.
 * </li>
 * </ul>
 * </p>
 * @return array An array of token identifiers. Each individual token identifier is either
 * a single character (i.e.: ;, .,
 * &gt;, !, etc...),
 * or a three element array containing the token index in element 0, the string
 * content of the original token in element 1 and the line number in element 2.
 */
#[Pure]
function token_get_all (string $code, int $flags = 0): array {}

/**
 * Get the symbolic name of a given PHP token
 * @link https://php.net/manual/en/function.token-name.php
 * @param int $id <p>
 * The token value.
 * </p>
 * @return string The symbolic name of the given <i>token</i>.
 */
#[Pure]
function token_name (int $id): string {}

define('TOKEN_PARSE', 1);
define('T_REQUIRE_ONCE', 263);
define('T_REQUIRE', 262);
define('T_EVAL', 321);
define('T_INCLUDE_ONCE', 261);
define('T_INCLUDE', 260);
define('T_LOGICAL_OR', 264);
define('T_LOGICAL_XOR', 265);
define('T_LOGICAL_AND', 266);
define('T_PRINT', 267);
define('T_YIELD', 268);
define('T_DOUBLE_ARROW', 269);
define('T_YIELD_FROM', 270);
define('T_POW_EQUAL', 282);
define('T_SR_EQUAL', 281);
define('T_SL_EQUAL', 280);
define('T_XOR_EQUAL', 279);
define('T_OR_EQUAL', 278);
define('T_AND_EQUAL', 277);
define('T_MOD_EQUAL', 276);
define('T_CONCAT_EQUAL', 275);
define('T_DIV_EQUAL', 274);
define('T_MUL_EQUAL', 273);
define('T_MINUS_EQUAL', 272);
define('T_PLUS_EQUAL', 271);
/**
 * @since 7.4
 */
define('T_COALESCE_EQUAL', 283);
define('T_COALESCE', 284);
define('T_BOOLEAN_OR', 285);
define('T_BOOLEAN_AND', 286);
define('T_SPACESHIP', 291);
define('T_IS_NOT_IDENTICAL', 290);
define('T_IS_IDENTICAL', 289);
define('T_IS_NOT_EQUAL', 288);
define('T_IS_EQUAL', 287);
define('T_IS_GREATER_OR_EQUAL', 293);
define('T_IS_SMALLER_OR_EQUAL', 292);
define('T_SR', 295);
define('T_SL', 294);
define('T_INSTANCEOF', 296);
define('T_UNSET_CAST', 303);
define('T_BOOL_CAST', 302);
define('T_OBJECT_CAST', 301);
define('T_ARRAY_CAST', 300);
define('T_STRING_CAST', 299);
define('T_DOUBLE_CAST', 298);
define('T_INT_CAST', 297);
define('T_DEC', 385);
define('T_INC', 384);
define('T_POW', 304);
define('T_CLONE', 305);
define('T_NEW', 322);
define('T_ELSEIF', 307);
define('T_ELSE', 308);
define('T_ENDIF', 325);
define('T_PUBLIC', 360);
define('T_PROTECTED', 359);
define('T_PRIVATE', 358);
define('T_FINAL', 357);
define('T_ABSTRACT', 356);
define('T_STATIC', 355);
define('T_LNUMBER', 309);
define('T_DNUMBER', 310);
define('T_STRING', 311);
define('T_VARIABLE', 315);
define('T_INLINE_HTML', 316);
define('T_ENCAPSED_AND_WHITESPACE', 317);
define('T_CONSTANT_ENCAPSED_STRING', 318);
define('T_STRING_VARNAME', 319);
define('T_NUM_STRING', 320);
define('T_EXIT', 323);
define('T_IF', 324);
define('T_ECHO', 326);
define('T_DO', 327);
define('T_WHILE', 328);
define('T_ENDWHILE', 329);
define('T_FOR', 330);
define('T_ENDFOR', 331);
define('T_FOREACH', 332);
define('T_ENDFOREACH', 333);
define('T_DECLARE', 334);
define('T_ENDDECLARE', 335);
define('T_AS', 336);
define('T_SWITCH', 337);
define('T_ENDSWITCH', 338);
define('T_CASE', 339);
define('T_DEFAULT', 340);
define('T_MATCH', 341);
define('T_BREAK', 342);
define('T_CONTINUE', 343);
define('T_GOTO', 344);
define('T_FUNCTION', 345);
define('T_CONST', 347);
define('T_RETURN', 348);
define('T_TRY', 349);
define('T_CATCH', 350);
define('T_FINALLY', 351);
define('T_THROW', 258);
define('T_USE', 352);
define('T_INSTEADOF', 353);
define('T_GLOBAL', 354);
define('T_VAR', 361);
define('T_UNSET', 362);
define('T_ISSET', 363);
define('T_EMPTY', 364);
define('T_HALT_COMPILER', 365);
define('T_CLASS', 366);
define('T_TRAIT', 367);
define('T_INTERFACE', 368);
define('T_EXTENDS', 369);
define('T_IMPLEMENTS', 370);
define('T_OBJECT_OPERATOR', 386);
define('T_LIST', 372);
define('T_ARRAY', 373);
define('T_CALLABLE', 374);
define('T_LINE', 375);
define('T_FILE', 376);
define('T_DIR', 377);
define('T_CLASS_C', 378);
define('T_TRAIT_C', 379);
define('T_METHOD_C', 380);
define('T_FUNC_C', 381);
define('T_COMMENT', 388);
define('T_DOC_COMMENT', 389);
define('T_OPEN_TAG', 390);
define('T_OPEN_TAG_WITH_ECHO', 391);
define('T_CLOSE_TAG', 392);
define('T_WHITESPACE', 393);
define('T_START_HEREDOC', 394);
define('T_END_HEREDOC', 395);
define('T_DOLLAR_OPEN_CURLY_BRACES', 396);
define('T_CURLY_OPEN', 397);
define('T_PAAMAYIM_NEKUDOTAYIM', 398);
define('T_NAMESPACE', 371);
define('T_NS_C', 382);
define('T_NS_SEPARATOR', 399);
define('T_ELLIPSIS', 400);
define('T_DOUBLE_COLON', 398);
/**
 * @since 7.4
 */
define('T_FN', 346);
define('T_BAD_CHARACTER', 401);

/**
 * @since 8.0
 */
define('T_NAME_FULLY_QUALIFIED', 312);
/**
 * @since 8.0
 */
define('T_NAME_RELATIVE', 313);
/**
 * @since 8.0
 */
define('T_NAME_QUALIFIED', 314);
/**
 * @since 8.0
 */
define('T_ATTRIBUTE', 383);
/**
 * @since 8.0
 */
define('T_NULLSAFE_OBJECT_OPERATOR', 387);

// End of tokenizer v.0.1
