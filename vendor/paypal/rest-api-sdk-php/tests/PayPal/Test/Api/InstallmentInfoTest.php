<?php

namespace PayPal\Test\Api;

use PayPal\Api\InstallmentInfo;

/**
 * Class InstallmentInfo
 *
 * @package PayPal\Test\Api
 */
class InstallmentInfoTest extends \PHPUnit_Framework_TestCase
{
    /**
     * Gets Json String of Object InstallmentInfo
     *
     * @return string
     */
    public static function getJson()
    {
        return '{"installment_id":"TestSample","network":"TestSample","issuer":"TestSample","installment_options":' . InstallmentOptionTest::getJson() . '}';
    }

    /**
     * Gets Object Instance with Json data filled in
     *
     * @return InstallmentInfo
     */
    public static function getObject()
    {
        return new InstallmentInfo(self::getJson());
    }


    /**
     * Tests for Serialization and Deserialization Issues
     *
     * @return InstallmentInfo
     */
    public function testSerializationDeserialization()
    {
        $obj = new InstallmentInfo(self::getJson());
        $this->assertNotNull($obj);
        $this->assertNotNull($obj->getInstallmentId());
        $this->assertNotNull($obj->getNetwork());
        $this->assertNotNull($obj->getIssuer());
        $this->assertNotNull($obj->getInstallmentOptions());
        $this->assertEquals(self::getJson(), $obj->toJson());
        return $obj;
    }

    /**
     * @depends testSerializationDeserialization
     * @param InstallmentInfo $obj
     */
    public function testGetters($obj)
    {
        $this->assertEquals($obj->getInstallmentId(), "TestSample");
        $this->assertEquals($obj->getNetwork(), "TestSample");
        $this->assertEquals($obj->getIssuer(), "TestSample");
        $this->assertEquals($obj->getInstallmentOptions(), InstallmentOptionTest::getObject());
    }


}
