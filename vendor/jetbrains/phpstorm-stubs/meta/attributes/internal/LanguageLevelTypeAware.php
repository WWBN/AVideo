<?php

namespace JetBrains\PhpStorm\Internal;

use Attribute;
use JetBrains\PhpStorm\Deprecated;
use JetBrains\PhpStorm\ExpectedValues;

/**
 * For PhpStorm internal use only
 * @since 8.0
 * @internal
 */
#[Attribute(Attribute::TARGET_FUNCTION | Attribute::TARGET_PARAMETER)]
class LanguageLevelTypeAware
{
    public function __construct(array $languageLevelTypeMap, string $default) {}
}
