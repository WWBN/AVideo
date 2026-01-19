<?php

namespace PayPal\Test\Api;

use PayPal\Api\PayoutBatch;

/**
 * Class PayoutBatch
 *
 * @package PayPal\Test\Api
 */
class PayoutBatchTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object PayoutBatch
     * @return string
     */
    public static function getJson()
    {
        return '{"batch_header":' .PayoutBatchHeaderTest::getJson() . ',"items":' .PayoutItemDetailsTest::getJson() . '}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return PayoutBatch
     */
    public static function getObject()
    {
        return new PayoutBatch(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return PayoutBatch
     */
    public function testSerializationDeserialization()
    {
        $obj = new PayoutBatch(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getBatchHeader());
        $this->assertNotNull($obj->getItems());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param PayoutBatch $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getBatchHeader(), PayoutBatchHeaderTest::getObject());
        $this->assertEquals($obj->getItems(), PayoutItemDetailsTest::getObject());
    }

}
