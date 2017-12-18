<?php

namespace Tests\YaMoney\Model\PaymentData;

use YaMoney\Model\PaymentData\PaymentDataWebmoney;
use YaMoney\Model\PaymentMethodType;

require_once __DIR__ . '/AbstractPaymentDataTest.php';

class PaymentDataWebmoneyTest extends AbstractPaymentDataTest
{
    /**
     * @return PaymentDataWebmoney
     */
    protected function getTestInstance()
    {
        return new PaymentDataWebmoney();
    }

    /**
     * @return string
     */
    protected function getExpectedType()
    {
        return PaymentMethodType::WEBMONEY;
    }
}