<?php

namespace PayPal\Test\Api;

use PayPal\Api\PayoutItemDetails;

/**
 * Class PayoutItemDetails
 *
 * @package PayPal\Test\Api
 */
class PayoutItemDetailsTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object PayoutItemDetails
     * @return string
     */
    public static function getJson()
    {
        return '{"payout_item_id":"TestSample","transaction_id":"TestSample","transaction_status":"TestSample","payout_item_fee":' .CurrencyTest::getJson() . ',"payout_batch_id":"TestSample","sender_batch_id":"TestSample","payout_item":' .PayoutItemTest::getJson() . ',"time_processed":"TestSample","errors":' .ErrorTest::getJson() . ',"links":' .LinksTest::getJson() . '}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return PayoutItemDetails
     */
    public static function getObject()
    {
        return new PayoutItemDetails(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return PayoutItemDetails
     */
    public function testSerializationDeserialization()
    {
        $obj = new PayoutItemDetails(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getPayoutItemId());
        $this->assertNotNull($obj->getTransactionId());
        $this->assertNotNull($obj->getTransactionStatus());
        $this->assertNotNull($obj->getPayoutItemFee());
        $this->assertNotNull($obj->getPayoutBatchId());
        $this->assertNotNull($obj->getSenderBatchId());
        $this->assertNotNull($obj->getPayoutItem());
        $this->assertNotNull($obj->getTimeProcessed());
        $this->assertNotNull($obj->getErrors());
        $this->assertNotNull($obj->getLinks());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param PayoutItemDetails $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getPayoutItemId(), "TestSample");
        $this->assertEquals($obj->getTransactionId(), "TestSample");
        $this->assertEquals($obj->getTransactionStatus(), "TestSample");
        $this->assertEquals($obj->getPayoutItemFee(), CurrencyTest::getObject());
        $this->assertEquals($obj->getPayoutBatchId(), "TestSample");
        $this->assertEquals($obj->getSenderBatchId(), "TestSample");
        $this->assertEquals($obj->getPayoutItem(), PayoutItemTest::getObject());
        $this->assertEquals($obj->getTimeProcessed(), "TestSample");
        $this->assertEquals($obj->getErrors(), ErrorTest::getObject());
        $this->assertEquals($obj->getLinks(), LinksTest::getObject());
    }

}
