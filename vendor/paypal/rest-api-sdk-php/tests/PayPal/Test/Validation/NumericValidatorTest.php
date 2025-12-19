<?php
namespace PayPal\Test\Validation;

use PayPal\Validation\NumericValidator;

class NumericValidatorTest extends \PHPUnit_Framework_TestCase
{

    public static function positiveProvider()
    {
        return array(
            array(".5", "0.50"),
            array(".55", "0.55"),
            array("0", "0.00"),
            array(null, null),
            array("01", "1.00"),
            array("01.1", "1.10"),
            array("10.0", "10.00"),
            array("0.0", "0.00"),
            array("00.00", "0.00"),
            array("000.111", "0.11"),
            array("000.0001", "0.00"),
            array("-0.001", "0.00"),
            array("-0", "0.00"),
            array("-00.00", "0.00"),
            array("-10.00", "-10.00"),
            array("", null),
            array("  ", null),
            array(1.20, "1.20")
        );
    }

    public static function invalidProvider()
    {
        return array(
            array("01.j"),
            array("j.10"),
            array("empty"),
            array("null")
        );
    }

    /**
     *
     * @dataProvider positiveProvider
     */
    public function testValidate($input)
    {
        $this->assertTrue(NumericValidator::validate($input, "Test Value"));
    }

    /**
     *
     * @dataProvider invalidProvider
     * @expectedException \InvalidArgumentException
     */
    public function testValidateException($input)
    {
       NumericValidator::validate($input, "Test Value");
    }

}
