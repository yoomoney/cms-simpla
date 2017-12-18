<?php

namespace Tests\YaMoney\Model\PaymentData;

use YaMoney\Model\PaymentData\PaymentDataAndroidPay;
use YaMoney\Model\PaymentMethodType;

require_once __DIR__ . '/AbstractPaymentDataMobileTest.php';

class PaymentDataAndroidPayTest extends AbstractPaymentDataMobileTest
{
    /**
     * @return PaymentDataAndroidPay
     */
    protected function getTestInstance()
    {
        return new PaymentDataAndroidPay();
    }

    /**
     * @return string
     */
    protected function getExpectedType()
    {
        return PaymentMethodType::ANDROID_PAY;
    }
}