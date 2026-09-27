<?php

namespace TelegramBotEssentials\GatewayAdmin\Telegram\CallbackQueries\Admin;

use Illuminate\Support\Facades\DB;
use Telegram\Bot\Exceptions\TelegramSDKException;
use TelegramBotEssentials\Billing\Models\Invoice;
use TelegramBotEssentials\Essence\Enums\Roles;
use TelegramBotEssentials\Essence\Models\MessageMeta;
use TelegramBotEssentials\Essence\Telegram\CallbackQueries\CallbackQuery;
use TelegramBotEssentials\GatewayAdmin\Models\AdminPaymentAttempt;

class AdminPaymentQuery extends CallbackQuery
{
    public const TYPE = 'ADMIN_PAYMENT';

    protected string $type = self::TYPE;

    protected int $perm = Roles::ADMIN->value;

    /**
     * @throws TelegramSDKException
     */
    public function pay(Invoice $invoice): void
    {
        // The row lock makes a double tap settle the invoice once.
        $attempt = DB::transaction(function () use ($invoice) {
            $fresh = Invoice::query()->whereKey($invoice->getKey())->lockForUpdate()->firstOrFail();
            if ($fresh->getAttribute('status') === 'paid') {
                return null;
            }

            $attempt = AdminPaymentAttempt::create([
                'bot_user_id' => wHook()->user()->getKey(),
                'amount' => $fresh->getAttribute('price'),
            ]);
            billing()->attemptPayment($fresh, $attempt);

            return $attempt;
        });

        if ($attempt === null) {
            wHook()->api()->answerCallbackQuery([
                'callback_query_id' => wHook()->update()->callbackQuery?->id,
                'text' => __('tbe-gateway-admin::invoice.alerts.already_paid'),
                'show_alert' => true,
            ]);

            return;
        }

        $attempt->attemptSucceed();

        tbeLog('gateway-admin')->audit('Paid invoice #{invoice_id} of {amount} as admin', [
            'invoice_id' => $invoice->getKey(),
            'attempt_id' => $attempt->getKey(),
            'amount' => $attempt->amount,
        ]);

        if ($invoice->messageMeta instanceof MessageMeta) {
            $invoice->messageMeta->lockAction(__('tbe-gateway-admin::invoice.locks.paid'), customEmoji: '✅');
        }
    }
}
