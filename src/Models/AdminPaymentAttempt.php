<?php

namespace TelegramBotEssentials\GatewayAdmin\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;
use TelegramBotEssentials\Billing\Models\Abstract\PaymentAttempt;
use TelegramBotEssentials\Essence\Models\BotUser;

/**
 * An invoice an admin settled with one click. No money moves: the attempt
 * only records who paid and for how much.
 *
 * @property int $bot_user_id
 * @property string $amount
 * @property string|null $status
 */
class AdminPaymentAttempt extends PaymentAttempt
{
    use BelongsToTenant;

    /** @return BelongsTo<BotUser, $this> */
    public function admin(): BelongsTo
    {
        return $this->belongsTo(BotUser::class, 'bot_user_id');
    }

    protected function attemptSucceedHook(): void {}

    protected function attemptFailedHook(): void {}
}
