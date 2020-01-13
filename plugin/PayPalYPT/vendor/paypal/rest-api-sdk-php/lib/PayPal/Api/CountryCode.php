<?php

namespace PayPal\Api;

use PayPal\Common\PayPalModel;

/**
 * Class CountryCode
 *
 * The ISO 3166-1 alpha-2 country code. A complete list of valid codes is available at Wikipedia: https://en.wikipedia.org/wiki/ISO_3166-1_alpha-2 In particular, note that the country code for Great Britain is GB, not UK as used in that country's top-level domain names.
 *
 * @package PayPal\Api
 *
 * @property string country_code
 */
class CountryCode extends PayPalModel
{
    /**
     * ISO country code based on 2-character IS0-3166-1 codes.
     *
     * @param string $country_code
     *
     * @return $this
     */
    public function setCountryCode($country_code)
    {
        $this->country_code = $country_code;
        return $this;
    }

    /**
     * ISO country code based on 2-character IS0-3166-1 codes.
     *
     * @return string
     */
    public function getCountryCode()
    {
        return $this->country_code;
    }

}
