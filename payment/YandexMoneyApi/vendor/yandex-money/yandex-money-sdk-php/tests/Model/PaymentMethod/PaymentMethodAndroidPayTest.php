<?php

namespace Tests\YaMoney\Model\PaymentMethod;

use YaMoney\Model\PaymentMethod\PaymentMethodAndroidPay;
use YaMoney\Model\PaymentMethodType;

require_once __DIR__ . '/AbstractPaymentMethodTest.php';

class PaymentMethodAndroidPayTest extends AbstractPaymentMethodTest
{
    /**
     * @return PaymentMethodAndroidPay
     */
    protected function getTestInstance()
    {
        return new PaymentMethodAndroidPay();
    }

    /**
     * @return string
     */
    protected function getExpectedType()
    {
        return PaymentMethodType::ANDROID_PAY;
    }
}