<?php

namespace Tests\YaMoney\Request\Payments\Payment;

use Tests\YaMoney\Request\Payments\AbstractPaymentResponseTest;
use YaMoney\Request\Payments\Payment\CancelResponse;

require_once __DIR__ . '/../AbstractPaymentResponseTest.php';

class CancelResponseTest extends AbstractPaymentResponseTest
{
    protected function getTestInstance($options)
    {
        return new CancelResponse($options);
    }
}