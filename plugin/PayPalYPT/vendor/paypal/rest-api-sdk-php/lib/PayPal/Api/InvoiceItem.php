<?php

namespace PayPal\Api;

use PayPal\Common\PayPalModel;
use PayPal\Converter\FormatConverter;
use PayPal\Validation\NumericValidator;
use PayPal\Validation\UrlValidator;

/**
 * Class InvoiceItem
 *
 * Information about a single line item.
 *
 * @package PayPal\Api
 *
 * @property string name
 * @property string description
 * @property \PayPal\Api\number quantity
 * @property \PayPal\Api\Currency unit_price
 * @property \PayPal\Api\Tax tax
 * @property string date
 * @property \PayPal\Api\Cost discount
 * @property string unit_of_measure
 */
class InvoiceItem extends PayPalModel
{
    /**
     * Name of the item. 200 characters max.
     *
     * @param string $name
     * 
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Name of the item. 200 characters max.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Description of the item. 1000 characters max.
     *
     * @param string $description
     * 
     * @return $this
     */
    public function setDescription($description)
    {
        $this->description = $description;
        return $this;
    }

    /**
     * Description of the item. 1000 characters max.
     *
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Quantity of the item. Range of -10000 to 10000.
     *
     * @param string|double $quantity
     * 
     * @return $this
     */
    public function setQuantity($quantity)
    {
        NumericValidator::validate($quantity, "Quantity");
        $quantity = FormatConverter::formatToPrice($quantity);
        $this->quantity = $quantity;
        return $this;
    }

    /**
     * Quantity of the item. Range of -10000 to 10000.
     *
     * @return string
     */
    public function getQuantity()
    {
        return $this->quantity;
    }

    /**
     * Unit price of the item. Range of -1,000,000 to 1,000,000.
     *
     * @param \PayPal\Api\Currency $unit_price
     * 
     * @return $this
     */
    public function setUnitPrice($unit_price)
    {
        $this->unit_price = $unit_price;
        return $this;
    }

    /**
     * Unit price of the item. Range of -1,000,000 to 1,000,000.
     *
     * @return \PayPal\Api\Currency
     */
    public function getUnitPrice()
    {
        return $this->unit_price;
    }

    /**
     * Tax associated with the item.
     *
     * @param \PayPal\Api\Tax $tax
     * 
     * @return $this
     */
    public function setTax($tax)
    {
        $this->tax = $tax;
        return $this;
    }

    /**
     * Tax associated with the item.
     *
     * @return \PayPal\Api\Tax
     */
    public function getTax()
    {
        return $this->tax;
    }

    /**
     * The date when the item or service was provided. The date format is *yyyy*-*MM*-*dd* *z* as defined in [Internet Date/Time Format](http://tools.ietf.org/html/rfc3339#section-5.6).
     *
     * @param string $date
     * 
     * @return $this
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * The date when the item or service was provided. The date format is *yyyy*-*MM*-*dd* *z* as defined in [Internet Date/Time Format](http://tools.ietf.org/html/rfc3339#section-5.6).
     *
     * @return string
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * The item discount, as a percent or an amount value.
     *
     * @param \PayPal\Api\Cost $discount
     * 
     * @return $this
     */
    public function setDiscount($discount)
    {
        $this->discount = $discount;
        return $this;
    }

    /**
     * The item discount, as a percent or an amount value.
     *
     * @return \PayPal\Api\Cost
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * The image URL. Maximum length is 4000 characters.
     * @deprecated Not publicly available
     * @param string $image_url
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function setImageUrl($image_url)
    {
        UrlValidator::validate($image_url, "ImageUrl");
        $this->image_url = $image_url;
        return $this;
    }

    /**
     * The image URL. Maximum length is 4000 characters.
     * @deprecated Not publicly available
     * @return string
     */
    public function getImageUrl()
    {
        return $this->image_url;
    }

    /**
     * The unit of measure of the item being invoiced.
     * Valid Values: ["QUANTITY", "HOURS", "AMOUNT"]
     *
     * @param string $unit_of_measure
     * 
     * @return $this
     */
    public function setUnitOfMeasure($unit_of_measure)
    {
        $this->unit_of_measure = $unit_of_measure;
        return $this;
    }

    /**
     * The unit of measure of the item being invoiced.
     *
     * @return string
     */
    public function getUnitOfMeasure()
    {
        return $this->unit_of_measure;
    }

}
