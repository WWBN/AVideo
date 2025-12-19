<?php

namespace PayPal\Test\Api;

use PayPal\Api\CarrierAccountToken;

/**
 * Class CarrierAccountToken
 *
 * @package PayPal\Test\Api
 */
class CarrierAccountTokenTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object CarrierAccountToken
     *
     * @return string
     */
    public static function getJson()
    {
        return '{"carrier_account_id":"TestSample","external_customer_id":"TestSample"}';
    }

    /**
     * Gets Object Instance with Json data filled in
     *
     * @return CarrierAccountToken
     */
    public static function getObject()
    {
        return new CarrierAccountToken(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     *
     * @return CarrierAccountToken
     */
    public function testSerializationDeserialization()
    {
        $obj = new CarrierAccountToken(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getCarrierAccountId());
        $this->assertNotNull($obj->getExternalCustomerId());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param CarrierAccountToken $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getCarrierAccountId(), "TestSample");
        $this->assertEquals($obj->getExternalCustomerId(), "TestSample");
    }


}
