<?php

namespace Tests\YaMoney\Model\Confirmation;

use YaMoney\Model\Confirmation\ConfirmationCodeVerification;
use YaMoney\Model\ConfirmationType;

require_once __DIR__ . '/AbstractConfirmationTest.php';

class ConfirmationCodeVerificationTest extends AbstractConfirmationTest
{
    /**
     * @return ConfirmationCodeVerification
     */
    protected function getTestInstance()
    {
        return new ConfirmationCodeVerification();
    }

    /**
     * @return string
     */
    protected function getExpectedType()
    {
        return ConfirmationType::CODE_VERIFICATION;
    }
}