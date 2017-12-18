<?php

namespace Tests\YaMoney\Model\PaymentMethod;

use YaMoney\Model\PaymentMethod\PaymentMethodMobileBalance;
use YaMoney\Model\PaymentMethodType;

require_once __DIR__ . '/AbstractPaymentMethodPhoneTest.php';

class PaymentMethodMobileBalanceTest extends AbstractPaymentMethodPhoneTest
{
    /**
     * @return PaymentMethodMobileBalance
     */
    protected function getTestInstance()
    {
        return new PaymentMethodMobileBalance();
    }

    /**
     * @return string
     */
    protected function getExpectedType()
    {
        return PaymentMethodType::MOBILE_BALANCE;
    }
}