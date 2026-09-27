<?php

namespace TelegramBotEssentials\GatewayAdmin;

use Illuminate\Support\ServiceProvider;
use Telegram\Bot\Keyboard\Keyboard;
use TelegramBotEssentials\Billing\DTOs\Gateway;
use TelegramBotEssentials\Billing\Models\Invoice;
use TelegramBotEssentials\GatewayAdmin\Telegram\CallbackQueries\Admin\AdminPaymentQuery;

class TbeGatewayAdminServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        $this->registerPublishing();

        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadTranslationsFrom(__DIR__.'/../lang', 'tbe-gateway-admin');

        callbackQueryBus()->addCallbackQueries([
            AdminPaymentQuery::class,
        ]);

        $this->registerToBilling();
    }

    protected function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../lang' => resource_path('lang/vendor/tbe-gateway-admin'),
            ], 'tbe-gateway-admin-translations');
        }
    }

    /** A button on every invoice, seen only by admins and the bot owner, that settles it in one click. */
    private function registerToBilling(): void
    {
        gateways()->addGateway(new Gateway(
            key: 'admin',
            label: __('tbe-gateway-admin::invoice.labels.gateway'),
            inlineButtonGenerator: function (Invoice $invoice) {
                if (! hasAccess()) {
                    return null;
                }

                return Keyboard::inlineButton([
                    'text' => __('tbe-gateway-admin::invoice.keys.pay'),
                    'callback_data' => encodeCallback(AdminPaymentQuery::TYPE, 'pay', [$invoice->getKey()]),
                ]);
            }
        ));
    }
}
