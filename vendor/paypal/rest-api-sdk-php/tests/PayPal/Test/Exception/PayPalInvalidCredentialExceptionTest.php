<?php
use PayPal\Exception\PayPalInvalidCredentialException;

/**
 * Test class for PayPalInvalidCredentialException.
 *
 */
class PayPalInvalidCredentialExceptionTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var PayPalInvalidCredentialException
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new PayPalInvalidCredentialException;
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
    }

    /**
     * @test
     */
    public function testErrorMessage()
    {
        $msg = $this->object->errorMessage();
        $this->assertContains('Error on line', $msg);
    }
}

?>
