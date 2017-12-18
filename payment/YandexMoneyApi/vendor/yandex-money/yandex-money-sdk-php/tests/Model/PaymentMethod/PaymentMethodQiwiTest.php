<?php

namespace Tests\YaMoney\Model\PaymentMethod;

use YaMoney\Model\PaymentMethod\PaymentMethodQiwi;
use YaMoney\Model\PaymentMethodType;

require_once __DIR__ . '/AbstractPaymentMethodTest.php';

class PaymentMethodQiwiTest extends AbstractPaymentMethodTest
{
    /**
     * @return PaymentMethodQiwi
     */
    protected function getTestInstance()
    {
        return new PaymentMethodQiwi();
    }

    /**
     * @return string
     */
    protected function getExpectedType()
    {
        return PaymentMethodType::QIWI;
    }
}