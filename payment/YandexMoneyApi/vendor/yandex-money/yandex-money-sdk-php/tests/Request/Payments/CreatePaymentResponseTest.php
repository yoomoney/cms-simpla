<?php

namespace Tests\YaMoney\Request\Payments;

use YaMoney\Request\Payments\CreatePaymentResponse;

require_once __DIR__ . '/AbstractPaymentResponseTest.php';

class CreatePaymentResponseTest extends AbstractPaymentResponseTest
{
    protected function getTestInstance($options)
    {
        return new CreatePaymentResponse($options);
    }
}