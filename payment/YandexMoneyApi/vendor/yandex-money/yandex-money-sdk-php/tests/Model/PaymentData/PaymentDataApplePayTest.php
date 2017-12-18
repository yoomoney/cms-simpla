<?php

namespace Tests\YaMoney\Model\PaymentData;

use YaMoney\Model\PaymentData\PaymentDataApplePay;
use YaMoney\Model\PaymentMethodType;

require_once __DIR__ . '/AbstractPaymentDataMobileTest.php';

class PaymentDataApplePayTest extends AbstractPaymentDataMobileTest
{
    /**
     * @return PaymentDataApplePay
     */
    protected function getTestInstance()
    {
        return new PaymentDataApplePay();
    }

    /**
     * @return string
     */
    protected function getExpectedType()
    {
        return PaymentMethodType::APPLE_PAY;
    }
}