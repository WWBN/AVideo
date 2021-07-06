<?php

namespace PayPal\Api;

use PayPal\Common\PayPalModel;

/**
 * Class Presentation
 *
 * Parameters for style and presentation.
 *
 * @package PayPal\Api
 *
 * @property string brand_name
 * @property string logo_image
 * @property string locale_code
 * @property string return_url_label
 * @property string note_to_seller_label
 */
class Presentation extends PayPalModel
{
    /**
     * A label that overrides the business name in the PayPal account on the PayPal pages. Character length and limitations: 127 single-byte alphanumeric characters.
     *
     * @param string $brand_name
     * 
     * @return $this
     */
    public function setBrandName($brand_name)
    {
        $this->brand_name = $brand_name;
        return $this;
    }

    /**
     * A label that overrides the business name in the PayPal account on the PayPal pages. Character length and limitations: 127 single-byte alphanumeric characters.
     *
     * @return string
     */
    public function getBrandName()
    {
        return $this->brand_name;
    }

    /**
     * A URL to the logo image. A valid media type is `.gif`, `.jpg`, or `.png`. The maximum width of the image is 190 pixels. The maximum height of the image is 60 pixels. PayPal crops images that are larger. PayPal places your logo image at the top of the cart review area. PayPal recommends that you store the image on a secure (HTTPS) server. Otherwise, web browsers display a message that checkout pages contain non-secure items. Character length and limit: 127 single-byte alphanumeric characters.
     *
     * @param string $logo_image
     * 
     * @return $this
     */
    public function setLogoImage($logo_image)
    {
        $this->logo_image = $logo_image;
        return $this;
    }

    /**
     * A URL to the logo image. A valid media type is `.gif`, `.jpg`, or `.png`. The maximum width of the image is 190 pixels. The maximum height of the image is 60 pixels. PayPal crops images that are larger. PayPal places your logo image at the top of the cart review area. PayPal recommends that you store the image on a secure (HTTPS) server. Otherwise, web browsers display a message that checkout pages contain non-secure items. Character length and limit: 127 single-byte alphanumeric characters.
     *
     * @return string
     */
    public function getLogoImage()
    {
        return $this->logo_image;
    }

    /**
     * The locale of pages displayed by PayPal payment experience. A valid value is `AU`, `AT`, `BE`, `BR`, `CA`, `CH`, `CN`, `DE`, `ES`, `GB`, `FR`, `IT`, `NL`, `PL`, `PT`, `RU`, or `US`. A 5-character code is also valid for languages in specific countries: `da_DK`, `he_IL`, `id_ID`, `ja_JP`, `no_NO`, `pt_BR`, `ru_RU`, `sv_SE`, `th_TH`, `zh_CN`, `zh_HK`, or `zh_TW`.
     *
     * @param string $locale_code
     * 
     * @return $this
     */
    public function setLocaleCode($locale_code)
    {
        $this->locale_code = $locale_code;
        return $this;
    }

    /**
     * The locale of pages displayed by PayPal payment experience. A valid value is `AU`, `AT`, `BE`, `BR`, `CA`, `CH`, `CN`, `DE`, `ES`, `GB`, `FR`, `IT`, `NL`, `PL`, `PT`, `RU`, or `US`. A 5-character code is also valid for languages in specific countries: `da_DK`, `he_IL`, `id_ID`, `ja_JP`, `no_NO`, `pt_BR`, `ru_RU`, `sv_SE`, `th_TH`, `zh_CN`, `zh_HK`, or `zh_TW`.
     *
     * @return string
     */
    public function getLocaleCode()
    {
        return $this->locale_code;
    }

    /**
     * A label to use as hypertext for the return to merchant link.
     *
     * @param string $return_url_label
     * 
     * @return $this
     */
    public function setReturnUrlLabel($return_url_label)
    {
        $this->return_url_label = $return_url_label;
        return $this;
    }

    /**
     * A label to use as hypertext for the return to merchant link.
     *
     * @return string
     */
    public function getReturnUrlLabel()
    {
        return $this->return_url_label;
    }

    /**
     * A label to use as the title for the note to seller field. Used only when `allow_note` is `1`.
     *
     * @param string $note_to_seller_label
     * 
     * @return $this
     */
    public function setNoteToSellerLabel($note_to_seller_label)
    {
        $this->note_to_seller_label = $note_to_seller_label;
        return $this;
    }

    /**
     * A label to use as the title for the note to seller field. Used only when `allow_note` is `1`.
     *
     * @return string
     */
    public function getNoteToSellerLabel()
    {
        return $this->note_to_seller_label;
    }

}
