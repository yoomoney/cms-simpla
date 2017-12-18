<?php

namespace Tests\YaMoney\Model\ConfirmationAttributes;

use YaMoney\Model\ConfirmationAttributes\ConfirmationAttributesDeepLink;
use YaMoney\Model\ConfirmationType;

require_once __DIR__ . '/AbstractConfirmationAttributesTest.php';

class ConfirmationAttributesDeepLinkTest extends AbstractConfirmationAttributesTest
{
    /**
     * @return ConfirmationAttributesDeepLink
     */
    protected function getTestInstance()
    {
        return new ConfirmationAttributesDeepLink();
    }

    /**
     * @return string
     */
    protected function getExpectedType()
    {
        return ConfirmationType::DEEPLINK;
    }
}