<?php

namespace PayPal\Api;

use PayPal\Common\PayPalModel;

/**
 * Class InputFields
 *
 * Parameters for input fields customization.
 *
 * @package PayPal\Api
 *
 * @property bool allow_note
 * @property int no_shipping
 * @property int address_override
 */
class InputFields extends PayPalModel
{
    /**
     * Indicates whether the buyer can enter a note to the merchant on the PayPal page during checkout.
     *
     * @param bool $allow_note
     * 
     * @return $this
     */
    public function setAllowNote($allow_note)
    {
        $this->allow_note = $allow_note;
        return $this;
    }

    /**
     * Indicates whether the buyer can enter a note to the merchant on the PayPal page during checkout.
     *
     * @return bool
     */
    public function getAllowNote()
    {
        return $this->allow_note;
    }

    /**
     * Indicates whether PayPal displays shipping address fields on the experience pages. Valid value is `0`, `1`, or `2`. Set to `0` to display the shipping address on the PayPal pages. Set to `1` to redact shipping address fields from the PayPal pages. Set to `2` to not pass the shipping address but instead get it from the buyer's account profile. For digital goods, this field is required and value must be `1`.
     *
     * @param int $no_shipping
     * 
     * @return $this
     */
    public function setNoShipping($no_shipping)
    {
        $this->no_shipping = $no_shipping;
        return $this;
    }

    /**
     * Indicates whether PayPal displays shipping address fields on the experience pages. Valid value is `0`, `1`, or `2`. Set to `0` to display the shipping address on the PayPal pages. Set to `1` to redact shipping address fields from the PayPal pages. Set to `2` to not pass the shipping address but instead get it from the buyer's account profile. For digital goods, this field is required and value must be `1`.
     *
     * @return int
     */
    public function getNoShipping()
    {
        return $this->no_shipping;
    }

    /**
     * Indicates whether to display the shipping address that is passed to this call rather than the one on file with PayPal for this buyer on the PayPal experience pages. Valid value is `0` or `1`. Set to `0` to display the shipping address on file. Set to `1` to display the shipping address supplied to this call; the buyer cannot edit this shipping address.
     *
     * @param int $address_override
     * 
     * @return $this
     */
    public function setAddressOverride($address_override)
    {
        $this->address_override = $address_override;
        return $this;
    }

    /**
     * Indicates whether to display the shipping address that is passed to this call rather than the one on file with PayPal for this buyer on the PayPal experience pages. Valid value is `0` or `1`. Set to `0` to display the shipping address on file. Set to `1` to display the shipping address supplied to this call; the buyer cannot edit this shipping address.
     *
     * @return int
     */
    public function getAddressOverride()
    {
        return $this->address_override;
    }

}
