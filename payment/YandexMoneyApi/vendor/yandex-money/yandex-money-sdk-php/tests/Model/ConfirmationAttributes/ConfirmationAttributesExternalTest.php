<?php

namespace Tests\YaMoney\Model\ConfirmationAttributes;

use YaMoney\Model\ConfirmationAttributes\ConfirmationAttributesExternal;
use YaMoney\Model\ConfirmationType;

require_once __DIR__ . '/AbstractConfirmationAttributesTest.php';

class ConfirmationAttributesExternalTest extends AbstractConfirmationAttributesTest
{
    /**
     * @return ConfirmationAttributesExternal
     */
    protected function getTestInstance()
    {
        return new ConfirmationAttributesExternal();
    }

    /**
     * @return string
     */
    protected function getExpectedType()
    {
        return ConfirmationType::EXTERNAL;
    }
}