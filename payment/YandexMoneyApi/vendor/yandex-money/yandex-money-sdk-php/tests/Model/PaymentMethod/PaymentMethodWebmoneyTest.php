<?php

namespace Tests\YaMoney\Model\PaymentMethod;

use YaMoney\Model\PaymentMethod\PaymentMethodWebmoney;
use YaMoney\Model\PaymentMethodType;

require_once __DIR__ . '/AbstractPaymentMethodTest.php';

class PaymentMethodWebmoneyTest extends AbstractPaymentMethodTest
{
    /**
     * @return PaymentMethodWebmoney
     */
    protected function getTestInstance()
    {
        return new PaymentMethodWebmoney();
    }

    /**
     * @return string
     */
    protected function getExpectedType()
    {
        return PaymentMethodType::WEBMONEY;
    }
}