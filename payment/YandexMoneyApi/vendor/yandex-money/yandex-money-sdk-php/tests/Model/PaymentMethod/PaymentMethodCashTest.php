<?php

namespace Tests\YaMoney\Model\PaymentMethod;

use YaMoney\Model\PaymentMethod\PaymentMethodCash;
use YaMoney\Model\PaymentMethodType;

require_once __DIR__ . '/AbstractPaymentMethodTest.php';

class PaymentMethodCashTest extends AbstractPaymentMethodTest
{
    /**
     * @return PaymentMethodCash
     */
    protected function getTestInstance()
    {
        return new PaymentMethodCash();
    }

    /**
     * @return string
     */
    protected function getExpectedType()
    {
        return PaymentMethodType::CASH;
    }
}