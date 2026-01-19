<?php
namespace PayPal\Test\Api;

use PayPal\Api\CreditCard;
use PayPal\Api\CreditCardHistory;

class CreditCardHistoryTest extends \PHPUnit_Framework_TestCase
{

    private $cards;

    public static $id = "id";
    public static $validUntil = "2013-02-28T00:00:00Z";
    public static $state = "created";
    public static $payerId = "payer-id";
    public static $cardType = "visa";
    public static $cardNumber = "4417119669820331";
    public static $expireMonth = 11;
    public static $expireYear = "2019";
    public static $cvv = "012";
    public static $firstName = "V";
    public static $lastName = "C";

    public static function createCreditCard()
    {
        $card = new CreditCard();
        $card->setType(self::$cardType);
        $card->setNumber(self::$cardNumber);
        $card->setExpireMonth(self::$expireMonth);
        $card->setExpireYear(self::$expireYear);
        $card->setCvv2(self::$cvv);
        $card->setFirstName(self::$firstName);
        $card->setLastName(self::$lastName);
        $card->setId(self::$id);
        $card->setValidUntil(self::$validUntil);
        $card->setState(self::$state);
        return $card;
    }

    public function setup()
    {

        $card = self::createCreditCard();
        $card->setBillingAddress(AddressTest::getObject());
        $card->setLinks(array(LinksTest::getObject()));
        $this->cards['full'] = $card;

        $card = self::createCreditCard();
        $this->cards['partial'] = $card;
    }

    public function testGetterSetters()
    {
        $cardHistory = new CreditCardHistory();
        $cardHistory->setCreditCards(array($this->cards['partial'], $this->cards['full']));
        $cardHistory->setCount(2);

        $this->assertEquals(2, count($cardHistory->getCreditCards()));
    }


    public function testSerializationDeserialization()
    {
        $cardHistory = new CreditCardHistory();
        $cardHistory->setCreditCards(array($this->cards['partial'], $this->cards['full']));
        $cardHistory->setCount(2);

        $cardHistoryCopy = new CreditCardHistory();
        $cardHistoryCopy->fromJson($cardHistory->toJSON());

        $this->assertEquals($cardHistory, $cardHistoryCopy);
    }
}
