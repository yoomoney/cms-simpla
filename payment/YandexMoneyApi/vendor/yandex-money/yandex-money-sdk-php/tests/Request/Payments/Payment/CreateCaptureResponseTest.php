<?php

namespace Tests\YaMoney\Request\Payments\Payment;

use Tests\YaMoney\Request\Payments\AbstractPaymentResponseTest;
use YaMoney\Request\Payments\Payment\CreateCaptureResponse;

require_once __DIR__ . '/../AbstractPaymentResponseTest.php';

class CreateCaptureResponseTest extends AbstractPaymentResponseTest
{
    protected function getTestInstance($options)
    {
        return new CreateCaptureResponse($options);
    }
}
