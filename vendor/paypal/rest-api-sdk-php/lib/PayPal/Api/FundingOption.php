<?php

namespace PayPal\Api;

use PayPal\Common\PayPalModel;

/**
 * Class FundingOption
 *
 * specifies the funding option details.
 *
 * @package PayPal\Api
 *
 * @property string id
 * @property \PayPal\Api\FundingSource[] funding_sources
 * @property \PayPal\Api\FundingInstrument backup_funding_instrument
 * @property \PayPal\Api\CurrencyConversion currency_conversion
 * @property \PayPal\Api\InstallmentInfo installment_info
 * @property \PayPal\Api\Links[] links
 */
class FundingOption extends PayPalModel
{
    /**
     * id of the funding option.
     *
     * @param string $id
     * 
     * @return $this
     */
    public function setId($id)
    {
        $this->id = $id;
        return $this;
    }

    /**
     * id of the funding option.
     *
     * @return string
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * List of funding sources that contributes to a payment.
     *
     * @param \PayPal\Api\FundingSource[] $funding_sources
     * 
     * @return $this
     */
    public function setFundingSources($funding_sources)
    {
        $this->funding_sources = $funding_sources;
        return $this;
    }

    /**
     * List of funding sources that contributes to a payment.
     *
     * @return \PayPal\Api\FundingSource[]
     */
    public function getFundingSources()
    {
        return $this->funding_sources;
    }

    /**
     * Append FundingSources to the list.
     *
     * @param \PayPal\Api\FundingSource $fundingSource
     * @return $this
     */
    public function addFundingSource($fundingSource)
    {
        if (!$this->getFundingSources()) {
            return $this->setFundingSources(array($fundingSource));
        } else {
            return $this->setFundingSources(
                array_merge($this->getFundingSources(), array($fundingSource))
            );
        }
    }

    /**
     * Remove FundingSources from the list.
     *
     * @param \PayPal\Api\FundingSource $fundingSource
     * @return $this
     */
    public function removeFundingSource($fundingSource)
    {
        return $this->setFundingSources(
            array_diff($this->getFundingSources(), array($fundingSource))
        );
    }

    /**
     * Backup funding instrument which will be used for payment if primary fails.
     *
     * @param \PayPal\Api\FundingInstrument $backup_funding_instrument
     * 
     * @return $this
     */
    public function setBackupFundingInstrument($backup_funding_instrument)
    {
        $this->backup_funding_instrument = $backup_funding_instrument;
        return $this;
    }

    /**
     * Backup funding instrument which will be used for payment if primary fails.
     *
     * @return \PayPal\Api\FundingInstrument
     */
    public function getBackupFundingInstrument()
    {
        return $this->backup_funding_instrument;
    }

    /**
     * Currency conversion applicable to this funding option.
     *
     * @param \PayPal\Api\CurrencyConversion $currency_conversion
     * 
     * @return $this
     */
    public function setCurrencyConversion($currency_conversion)
    {
        $this->currency_conversion = $currency_conversion;
        return $this;
    }

    /**
     * Currency conversion applicable to this funding option.
     *
     * @return \PayPal\Api\CurrencyConversion
     */
    public function getCurrencyConversion()
    {
        return $this->currency_conversion;
    }

    /**
     * Installment options available for a funding option.
     *
     * @param \PayPal\Api\InstallmentInfo $installment_info
     * 
     * @return $this
     */
    public function setInstallmentInfo($installment_info)
    {
        $this->installment_info = $installment_info;
        return $this;
    }

    /**
     * Installment options available for a funding option.
     *
     * @return \PayPal\Api\InstallmentInfo
     */
    public function getInstallmentInfo()
    {
        return $this->installment_info;
    }

    /**
     * Sets Links
     *
     * @param \PayPal\Api\Links[] $links
     * 
     * @return $this
     */
    public function setLinks($links)
    {
        $this->links = $links;
        return $this;
    }

    /**
     * Gets Links
     *
     * @return \PayPal\Api\Links[]
     */
    public function getLinks()
    {
        return $this->links;
    }

    /**
     * Append Links to the list.
     *
     * @param \PayPal\Api\Links $links
     * @return $this
     */
    public function addLink($links)
    {
        if (!$this->getLinks()) {
            return $this->setLinks(array($links));
        } else {
            return $this->setLinks(
                array_merge($this->getLinks(), array($links))
            );
        }
    }

    /**
     * Remove Links from the list.
     *
     * @param \PayPal\Api\Links $links
     * @return $this
     */
    public function removeLink($links)
    {
        return $this->setLinks(
            array_diff($this->getLinks(), array($links))
        );
    }

}
