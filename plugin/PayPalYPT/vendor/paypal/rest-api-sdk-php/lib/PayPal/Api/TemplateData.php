<?php

namespace PayPal\Api;

use PayPal\Common\PayPalModel;
use PayPal\Validation\UrlValidator;

/**
 * Class TemplateData
 *
 * Detailed template information.
 *
 * @package PayPal\Api
 *
 * @property \PayPal\Api\MerchantInfo merchant_info
 * @property \PayPal\Api\BillingInfo[] billing_info
 * @property string[] cc_info
 * @property \PayPal\Api\ShippingInfo shipping_info
 * @property \PayPal\Api\InvoiceItem[] items
 * @property \PayPal\Api\PaymentTerm payment_term
 * @property string reference
 * @property \PayPal\Api\Cost discount
 * @property \PayPal\Api\ShippingCost shipping_cost
 * @property \PayPal\Api\CustomAmount custom
 * @property bool allow_partial_payment
 * @property \PayPal\Api\Currency minimum_amount_due
 * @property bool tax_calculated_after_discount
 * @property bool tax_inclusive
 * @property string terms
 * @property string note
 * @property string merchant_memo
 * @property string logo_url
 * @property \PayPal\Api\Currency total_amount
 * @property \PayPal\Api\FileAttachment[] attachments
 */
class TemplateData extends PayPalModel
{
    /**
     * Information about the merchant who is sending the invoice.
     *
     * @param \PayPal\Api\MerchantInfo $merchant_info
     * 
     * @return $this
     */
    public function setMerchantInfo($merchant_info)
    {
        $this->merchant_info = $merchant_info;
        return $this;
    }

    /**
     * Information about the merchant who is sending the invoice.
     *
     * @return \PayPal\Api\MerchantInfo
     */
    public function getMerchantInfo()
    {
        return $this->merchant_info;
    }

    /**
     * The required invoice recipient email address and any optional billing information. One recipient is supported.
     *
     * @param \PayPal\Api\BillingInfo[] $billing_info
     * 
     * @return $this
     */
    public function setBillingInfo($billing_info)
    {
        $this->billing_info = $billing_info;
        return $this;
    }

    /**
     * The required invoice recipient email address and any optional billing information. One recipient is supported.
     *
     * @return \PayPal\Api\BillingInfo[]
     */
    public function getBillingInfo()
    {
        return $this->billing_info;
    }

    /**
     * Append BillingInfo to the list.
     *
     * @param \PayPal\Api\BillingInfo $billingInfo
     * @return $this
     */
    public function addBillingInfo($billingInfo)
    {
        if (!$this->getBillingInfo()) {
            return $this->setBillingInfo(array($billingInfo));
        } else {
            return $this->setBillingInfo(
                array_merge($this->getBillingInfo(), array($billingInfo))
            );
        }
    }

    /**
     * Remove BillingInfo from the list.
     *
     * @param \PayPal\Api\BillingInfo $billingInfo
     * @return $this
     */
    public function removeBillingInfo($billingInfo)
    {
        return $this->setBillingInfo(
            array_diff($this->getBillingInfo(), array($billingInfo))
        );
    }

    /**
     * For invoices sent by email, one or more email addresses to which to send a Cc: copy of the notification. Supports only email addresses under participant.
     *
     * @param string[] $cc_info
     * 
     * @return $this
     */
    public function setCcInfo($cc_info)
    {
        $this->cc_info = $cc_info;
        return $this;
    }

    /**
     * For invoices sent by email, one or more email addresses to which to send a Cc: copy of the notification. Supports only email addresses under participant.
     *
     * @return string[]
     */
    public function getCcInfo()
    {
        return $this->cc_info;
    }

    /**
     * Append CcInfo to the list.
     *
     * @param string $email
     * @return $this
     */
    public function addCcInfo($email)
    {
        if (!$this->getCcInfo()) {
            return $this->setCcInfo(array($email));
        } else {
            return $this->setCcInfo(
                array_merge($this->getCcInfo(), array($email))
            );
        }
    }

    /**
     * Remove CcInfo from the list.
     *
     * @param string $email
     * @return $this
     */
    public function removeCcInfo($email)
    {
        return $this->setCcInfo(
            array_diff($this->getCcInfo(), array($email))
        );
    }

    /**
     * The shipping information for entities to whom items are being shipped.
     *
     * @param \PayPal\Api\ShippingInfo $shipping_info
     * 
     * @return $this
     */
    public function setShippingInfo($shipping_info)
    {
        $this->shipping_info = $shipping_info;
        return $this;
    }

    /**
     * The shipping information for entities to whom items are being shipped.
     *
     * @return \PayPal\Api\ShippingInfo
     */
    public function getShippingInfo()
    {
        return $this->shipping_info;
    }

    /**
     * The list of items to include in the invoice. Maximum value is 100 items per invoice.
     *
     * @param \PayPal\Api\InvoiceItem[] $items
     * 
     * @return $this
     */
    public function setItems($items)
    {
        $this->items = $items;
        return $this;
    }

    /**
     * The list of items to include in the invoice. Maximum value is 100 items per invoice.
     *
     * @return \PayPal\Api\InvoiceItem[]
     */
    public function getItems()
    {
        return $this->items;
    }

    /**
     * Append Items to the list.
     *
     * @param \PayPal\Api\InvoiceItem $invoiceItem
     * @return $this
     */
    public function addItem($invoiceItem)
    {
        if (!$this->getItems()) {
            return $this->setItems(array($invoiceItem));
        } else {
            return $this->setItems(
                array_merge($this->getItems(), array($invoiceItem))
            );
        }
    }

    /**
     * Remove Items from the list.
     *
     * @param \PayPal\Api\InvoiceItem $invoiceItem
     * @return $this
     */
    public function removeItem($invoiceItem)
    {
        return $this->setItems(
            array_diff($this->getItems(), array($invoiceItem))
        );
    }

    /**
     * Optional. The payment deadline for the invoice. Value is either `term_type` or `due_date` but not both.
     *
     * @param \PayPal\Api\PaymentTerm $payment_term
     * 
     * @return $this
     */
    public function setPaymentTerm($payment_term)
    {
        $this->payment_term = $payment_term;
        return $this;
    }

    /**
     * Optional. The payment deadline for the invoice. Value is either `term_type` or `due_date` but not both.
     *
     * @return \PayPal\Api\PaymentTerm
     */
    public function getPaymentTerm()
    {
        return $this->payment_term;
    }

    /**
     * Reference data, such as PO number, to add to the invoice. Maximum length is 60 characters.
     *
     * @param string $reference
     * 
     * @return $this
     */
    public function setReference($reference)
    {
        $this->reference = $reference;
        return $this;
    }

    /**
     * Reference data, such as PO number, to add to the invoice. Maximum length is 60 characters.
     *
     * @return string
     */
    public function getReference()
    {
        return $this->reference;
    }

    /**
     * The invoice level discount, as a percent or an amount value.
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
     * The invoice level discount, as a percent or an amount value.
     *
     * @return \PayPal\Api\Cost
     */
    public function getDiscount()
    {
        return $this->discount;
    }

    /**
     * The shipping cost, as a percent or an amount value.
     *
     * @param \PayPal\Api\ShippingCost $shipping_cost
     * 
     * @return $this
     */
    public function setShippingCost($shipping_cost)
    {
        $this->shipping_cost = $shipping_cost;
        return $this;
    }

    /**
     * The shipping cost, as a percent or an amount value.
     *
     * @return \PayPal\Api\ShippingCost
     */
    public function getShippingCost()
    {
        return $this->shipping_cost;
    }

    /**
     * The custom amount to apply on an invoice. If you include a label, the amount cannot be empty.
     *
     * @param \PayPal\Api\CustomAmount $custom
     * 
     * @return $this
     */
    public function setCustom($custom)
    {
        $this->custom = $custom;
        return $this;
    }

    /**
     * The custom amount to apply on an invoice. If you include a label, the amount cannot be empty.
     *
     * @return \PayPal\Api\CustomAmount
     */
    public function getCustom()
    {
        return $this->custom;
    }

    /**
     * Indicates whether the invoice allows a partial payment. If set to `false`, invoice must be paid in full. If set to `true`, the invoice allows partial payments. Default is `false`.
     *
     * @param bool $allow_partial_payment
     * 
     * @return $this
     */
    public function setAllowPartialPayment($allow_partial_payment)
    {
        $this->allow_partial_payment = $allow_partial_payment;
        return $this;
    }

    /**
     * Indicates whether the invoice allows a partial payment. If set to `false`, invoice must be paid in full. If set to `true`, the invoice allows partial payments. Default is `false`.
     *
     * @return bool
     */
    public function getAllowPartialPayment()
    {
        return $this->allow_partial_payment;
    }

    /**
     * If `allow_partial_payment` is set to `true`, the minimum amount allowed for a partial payment.
     *
     * @param \PayPal\Api\Currency $minimum_amount_due
     * 
     * @return $this
     */
    public function setMinimumAmountDue($minimum_amount_due)
    {
        $this->minimum_amount_due = $minimum_amount_due;
        return $this;
    }

    /**
     * If `allow_partial_payment` is set to `true`, the minimum amount allowed for a partial payment.
     *
     * @return \PayPal\Api\Currency
     */
    public function getMinimumAmountDue()
    {
        return $this->minimum_amount_due;
    }

    /**
     * Indicates whether tax is calculated before or after a discount. If set to `false`, the tax is calculated before a discount. If set to `true`, the tax is calculated after a discount. Default is `false`.
     *
     * @param bool $tax_calculated_after_discount
     * 
     * @return $this
     */
    public function setTaxCalculatedAfterDiscount($tax_calculated_after_discount)
    {
        $this->tax_calculated_after_discount = $tax_calculated_after_discount;
        return $this;
    }

    /**
     * Indicates whether tax is calculated before or after a discount. If set to `false`, the tax is calculated before a discount. If set to `true`, the tax is calculated after a discount. Default is `false`.
     *
     * @return bool
     */
    public function getTaxCalculatedAfterDiscount()
    {
        return $this->tax_calculated_after_discount;
    }

    /**
     * Indicates whether the unit price includes tax. Default is `false`.
     *
     * @param bool $tax_inclusive
     * 
     * @return $this
     */
    public function setTaxInclusive($tax_inclusive)
    {
        $this->tax_inclusive = $tax_inclusive;
        return $this;
    }

    /**
     * Indicates whether the unit price includes tax. Default is `false`.
     *
     * @return bool
     */
    public function getTaxInclusive()
    {
        return $this->tax_inclusive;
    }

    /**
     * General terms of the invoice. 4000 characters max.
     *
     * @param string $terms
     * 
     * @return $this
     */
    public function setTerms($terms)
    {
        $this->terms = $terms;
        return $this;
    }

    /**
     * General terms of the invoice. 4000 characters max.
     *
     * @return string
     */
    public function getTerms()
    {
        return $this->terms;
    }

    /**
     * Note to the payer. 4000 characters max.
     *
     * @param string $note
     * 
     * @return $this
     */
    public function setNote($note)
    {
        $this->note = $note;
        return $this;
    }

    /**
     * Note to the payer. 4000 characters max.
     *
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * A private bookkeeping memo for the merchant. Maximum length is 150 characters.
     *
     * @param string $merchant_memo
     * 
     * @return $this
     */
    public function setMerchantMemo($merchant_memo)
    {
        $this->merchant_memo = $merchant_memo;
        return $this;
    }

    /**
     * A private bookkeeping memo for the merchant. Maximum length is 150 characters.
     *
     * @return string
     */
    public function getMerchantMemo()
    {
        return $this->merchant_memo;
    }

    /**
     * Full URL of an external image to use as the logo. Maximum length is 4000 characters.
     *
     * @param string $logo_url
     * @throws \InvalidArgumentException
     * @return $this
     */
    public function setLogoUrl($logo_url)
    {
        UrlValidator::validate($logo_url, "LogoUrl");
        $this->logo_url = $logo_url;
        return $this;
    }

    /**
     * Full URL of an external image to use as the logo. Maximum length is 4000 characters.
     *
     * @return string
     */
    public function getLogoUrl()
    {
        return $this->logo_url;
    }

    /**
     * The total amount of the invoice.
     *
     * @param \PayPal\Api\Currency $total_amount
     * 
     * @return $this
     */
    public function setTotalAmount($total_amount)
    {
        $this->total_amount = $total_amount;
        return $this;
    }

    /**
     * The total amount of the invoice.
     *
     * @return \PayPal\Api\Currency
     */
    public function getTotalAmount()
    {
        return $this->total_amount;
    }

    /**
     * List of files attached to the invoice.
     *
     * @param \PayPal\Api\FileAttachment[] $attachments
     * 
     * @return $this
     */
    public function setAttachments($attachments)
    {
        $this->attachments = $attachments;
        return $this;
    }

    /**
     * List of files attached to the invoice.
     *
     * @return \PayPal\Api\FileAttachment[]
     */
    public function getAttachments()
    {
        return $this->attachments;
    }

    /**
     * Append Attachments to the list.
     *
     * @param \PayPal\Api\FileAttachment $fileAttachment
     * @return $this
     */
    public function addAttachment($fileAttachment)
    {
        if (!$this->getAttachments()) {
            return $this->setAttachments(array($fileAttachment));
        } else {
            return $this->setAttachments(
                array_merge($this->getAttachments(), array($fileAttachment))
            );
        }
    }

    /**
     * Remove Attachments from the list.
     *
     * @param \PayPal\Api\FileAttachment $fileAttachment
     * @return $this
     */
    public function removeAttachment($fileAttachment)
    {
        return $this->setAttachments(
            array_diff($this->getAttachments(), array($fileAttachment))
        );
    }

}
