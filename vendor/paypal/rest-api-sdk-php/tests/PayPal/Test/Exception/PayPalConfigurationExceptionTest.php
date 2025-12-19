<?php
use PayPal\Exception\PayPalConfigurationException;

/**
 * Test class for PayPalConfigurationException.
 *
 */
class PayPalConfigurationExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PayPalConfigurationException
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new PayPalConfigurationException('Test PayPalConfigurationException');
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    public function testPPConfigurationException()
    {
        $this->assertEquals('Test PayPalConfigurationException', $this->object->getMessage());
    }
}

?>
