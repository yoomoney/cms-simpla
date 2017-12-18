<?php

namespace Tests\YaMoney\Model\Confirmation;

use YaMoney\Model\Confirmation\ConfirmationExternal;
use YaMoney\Model\ConfirmationType;

require_once __DIR__ . '/AbstractConfirmationTest.php';

class ConfirmationExternalTest extends AbstractConfirmationTest
{
    /**
     * @return ConfirmationExternal
     */
    protected function getTestInstance()
    {
        return new ConfirmationExternal();
    }

    /**
     * @return string
     */
    protected function getExpectedType()
    {
        return ConfirmationType::EXTERNAL;
    }
}