<?php

namespace Tests\YaMoney\Model\Confirmation;

use YaMoney\Model\Confirmation\ConfirmationDeepLink;
use YaMoney\Model\ConfirmationType;

require_once __DIR__ . '/AbstractConfirmationTest.php';

class ConfirmationDeepLinkTest extends AbstractConfirmationTest
{
    /**
     * @return ConfirmationDeepLink
     */
    protected function getTestInstance()
    {
        return new ConfirmationDeepLink();
    }

    /**
     * @return string
     */
    protected function getExpectedType()
    {
        return ConfirmationType::DEEPLINK;
    }
}