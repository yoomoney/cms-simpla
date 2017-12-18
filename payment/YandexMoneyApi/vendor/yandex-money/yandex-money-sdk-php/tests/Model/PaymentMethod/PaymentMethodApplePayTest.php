<?php

namespace Tests\YaMoney\Model\PaymentMethod;

use YaMoney\Model\PaymentMethod\PaymentMethodApplePay;
use YaMoney\Model\PaymentMethodType;

require_once __DIR__ . '/AbstractPaymentMethodTest.php';

class PaymentMethodApplePayTest extends AbstractPaymentMethodTest
{
    /**
     * @return PaymentMethodApplePay
     */
    protected function getTestInstance()
    {
        return new PaymentMethodApplePay();
    }

    /**
     * @return string
     */
    protected function getExpectedType()
    {
        return PaymentMethodType::APPLE_PAY;
    }
}