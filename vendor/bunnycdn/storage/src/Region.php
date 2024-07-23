<?php

declare(strict_types=1);

namespace Bunny\Storage;

class Region
{
    public const FALKENSTEIN = 'de';
    public const LONDON = 'uk';
    public const STOCKHOLM = 'se';
    public const NEW_YORK = 'ny';
    public const LOS_ANGELES = 'la';
    public const SINGAPORE = 'sg';
    public const SYDNEY = 'syd';
    public const SAO_PAULO = 'br';
    public const JOHANNESBURG = 'jh';

    public const LIST = [
        self::FALKENSTEIN => 'Europe (Falkenstein)',
        self::LONDON => 'Europe (London)',
        self::STOCKHOLM => 'Europe (Stockholm)',
        self::NEW_YORK => 'US East (New York)',
        self::LOS_ANGELES => 'US West (Los Angeles)',
        self::SINGAPORE => 'Asia (Singapore)',
        self::SYDNEY => 'Oceania (Sydney)',
        self::SAO_PAULO => 'LATAM (Sao Paulo)',
        self::JOHANNESBURG => 'Africa (Johannesburg)',
    ];

    public static function getBaseUrl(string $region): string
    {
        if (!isset(Region::LIST[$region])) {
            throw new InvalidRegionException();
        }

        if ('de' === $region) {
            return 'https://storage.bunnycdn.com/';
        }

        return sprintf('https://%s.storage.bunnycdn.com/', $region);
    }
}
