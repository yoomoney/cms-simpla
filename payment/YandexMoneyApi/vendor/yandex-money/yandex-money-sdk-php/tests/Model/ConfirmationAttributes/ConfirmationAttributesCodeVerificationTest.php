<?php

namespace Tests\YaMoney\Model\ConfirmationAttributes;

use YaMoney\Model\ConfirmationAttributes\ConfirmationAttributesCodeVerification;
use YaMoney\Model\ConfirmationType;

require_once __DIR__ . '/AbstractConfirmationAttributesTest.php';

class ConfirmationAttributesCodeVerificationTest extends AbstractConfirmationAttributesTest
{
    /**
     * @return ConfirmationAttributesCodeVerification
     */
    protected function getTestInstance()
    {
        return new ConfirmationAttributesCodeVerification();
    }

    /**
     * @return string
     */
    protected function getExpectedType()
    {
        return ConfirmationType::CODE_VERIFICATION;
    }
}
