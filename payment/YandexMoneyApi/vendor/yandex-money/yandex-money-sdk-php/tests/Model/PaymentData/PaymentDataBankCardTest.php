<?php

namespace Tests\YaMoney\Model\PaymentData;

use YaMoney\Helpers\Random;
use YaMoney\Model\PaymentData\PaymentDataBankCard;
use YaMoney\Model\PaymentData\PaymentDataBankCardCard;
use YaMoney\Model\PaymentMethodType;

require_once __DIR__ . '/AbstractPaymentDataTest.php';

class PaymentDataBankCardTest extends AbstractPaymentDataTest
{
    /**
     * @return PaymentDataBankCard
     */
    protected function getTestInstance()
    {
        return new PaymentDataBankCard();
    }

    /**
     * @return string
     */
    protected function getExpectedType()
    {
        return PaymentMethodType::BANK_CARD;
    }

    /**
     * @dataProvider validCardDataProvider
     * @param PaymentDataBankCardCard $value
     */
    public function testGetSetBankCard($value)
    {
        $instance = $this->getTestInstance();

        self::assertNull($instance->getBankCard());
        self::assertNull($instance->bankCard);

        $instance->setBankCard($value);
        if ($value === null || $value === '' || $value === array()) {
            self::assertNull($instance->getBankCard());
            self::assertNull($instance->bankCard);
        } else {
            if (is_array($value)) {
                $expected = new PaymentDataBankCardCard();
                foreach ($value as $property => $val) {
                    $expected->offsetSet($property, $val);
                }
            } else {
                $expected = $value;
            }
            self::assertEquals($expected, $instance->getBankCard());
            self::assertEquals($expected, $instance->bankCard);
        }

        $instance = $this->getTestInstance();
        $instance->bankCard = $value;
        if ($value === null || $value === '' || $value === array()) {
            self::assertNull($instance->getBankCard());
            self::assertNull($instance->bankCard);
        } else {
            if (is_array($value)) {
                $expected = new PaymentDataBankCardCard();
                foreach ($value as $property => $val) {
                    $expected->offsetSet($property, $val);
                }
            } else {
                $expected = $value;
            }
            self::assertEquals($expected, $instance->getBankCard());
            self::assertEquals($expected, $instance->bankCard);
        }
    }

    /**
     * @dataProvider invalidCardDataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetInvalidCard($value)
    {
        $this->getTestInstance()->setBankCard($value);
    }

    /**
     * @dataProvider invalidCardDataProvider
     * @expectedException \InvalidArgumentException
     * @param mixed $value
     */
    public function testSetterInvalidCard($value)
    {
        $this->getTestInstance()->bankCard = $value;
    }

    public function validCardDataProvider()
    {
        return array(
            array(null),
            array(new PaymentDataBankCardCard()),
            array(array()),
            array(''),
            array(array(
                'number' => Random::str(16, '0123456789'),
            )),
        );
    }

    public function invalidCardDataProvider()
    {
        return array(
            array(0),
            array(1),
            array(-1),
            array('5'),
            array(true),
            array(new \stdClass()),
        );
    }
}