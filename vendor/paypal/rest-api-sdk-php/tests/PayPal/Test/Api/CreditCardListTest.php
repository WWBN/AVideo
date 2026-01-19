<?php

namespace PayPal\Test\Api;

use PayPal\Api\CreditCardList;

/**
 * Class CreditCardList
 *
 * @package PayPal\Test\Api
 */
class CreditCardListTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object CreditCardList
     * @return string
     */
    public static function getJson()
    {
        return '{"items":' .CreditCardTest::getJson() . ',"links":' .LinksTest::getJson() . ',"total_items":123,"total_pages":123}';
    }

    /**
     * Gets Object Instance with Json data filled in
     * @return CreditCardList
     */
    public static function getObject()
    {
        return new CreditCardList(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     * @return CreditCardList
     */
    public function testSerializationDeserialization()
    {
        $obj = new CreditCardList(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getItems());
        $this->assertNotNull($obj->getLinks());
        $this->assertNotNull($obj->getTotalItems());
        $this->assertNotNull($obj->getTotalPages());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param CreditCardList $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getItems(), CreditCardTest::getObject());
        $this->assertEquals($obj->getLinks(), LinksTest::getObject());
        $this->assertEquals($obj->getTotalItems(), 123);
        $this->assertEquals($obj->getTotalPages(), 123);
    }

}
