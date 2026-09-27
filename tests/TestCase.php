<?php

declare(strict_types=1);

namespace TelegramBotEssentials\GatewayAdmin\Tests;

use TelegramBotEssentials\Essence\Testing\TestCase as EssenceTestCase;
use TelegramBotEssentials\GatewayAdmin\TbeGatewayAdminServiceProvider;

abstract class TestCase extends EssenceTestCase
{
    protected function getPackageProviders($app): array
    {
        return array_merge(parent::getPackageProviders($app), [
            TbeGatewayAdminServiceProvider::class,
        ]);
    }
}
