<?php

declare(strict_types=1);

namespace TelegramBotEssentials\GatewayAdmin\Tests;

use TelegramBotEssentials\Billing\TbeBillingServiceProvider;
use TelegramBotEssentials\Essence\Testing\TestCase as EssenceTestCase;
use TelegramBotEssentials\GatewayAdmin\TbeGatewayAdminServiceProvider;
use TelegramBotEssentials\Settings\TbeSettingsServiceProvider;

abstract class TestCase extends EssenceTestCase
{
    protected function getPackageProviders($app): array
    {
        return array_merge(parent::getPackageProviders($app), [
            TbeSettingsServiceProvider::class,
            TbeBillingServiceProvider::class,
            TbeGatewayAdminServiceProvider::class,
        ]);
    }
}
