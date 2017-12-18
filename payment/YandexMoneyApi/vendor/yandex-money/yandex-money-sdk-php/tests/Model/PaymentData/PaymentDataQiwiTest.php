<?php

namespace Tests\YaMoney\Model\PaymentData;

use YaMoney\Model\PaymentData\PaymentDataQiwi;
use YaMoney\Model\PaymentMethodType;

require_once __DIR__ . '/AbstractPaymentDataTest.php';

class PaymentDataQiwiTest extends AbstractPaymentDataPhoneTest
{
    /**
     * @return PaymentDataQiwi
     */
    protected function getTestInstance()
    {
        return new PaymentDataQiwi();
    }

    /**
     * @return string
     */
    protected function getExpectedType()
    {
        return PaymentMethodType::QIWI;
    }
}