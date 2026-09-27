<?php

declare(strict_types=1);

use Illuminate\Http\Client\Request;
use TelegramBotEssentials\Billing\Models\Invoice;
use TelegramBotEssentials\Essence\Enums\Roles;
use TelegramBotEssentials\Essence\Models\Bot;
use TelegramBotEssentials\Essence\Models\BotUser;
use TelegramBotEssentials\Essence\Support\WebhookContext;
use TelegramBotEssentials\GatewayAdmin\Models\AdminPaymentAttempt;

/** A pending invoice of a fresh member of the bot. */
function pendingInvoice(Bot $bot, string $price = '150000'): Invoice
{
    $member = test()->makeBotUser($bot, random_int(100000, 999999999));

    // payable points at the member itself: a real, resolvable morph target
    // that is unique per call, so Invoice::booted() soft-deletes nothing.
    return Invoice::create([
        'bot_id' => $bot->id,
        'bot_user_id' => $member->id,
        'price' => $price,
        'payable_type' => BotUser::class,
        'payable_id' => $member->id,
    ]);
}

function viewAs(BotUser $viewer): void
{
    (new WebhookContext(botId: $viewer->bot_id, botUserId: $viewer->id, bot: $viewer->bot, botUser: $viewer))->apply();
}

function adminButton(Invoice $invoice): mixed
{
    return gateways()->getGateway('admin')->getInlineKeyboard($invoice);
}

function pressPay(Bot $bot, Invoice $invoice, int $peerId): void
{
    test()->postWebhookUpdate($bot, test()->makeCallbackQueryUpdate(encodeCallback('ADMIN_PAYMENT', 'pay', [$invoice->id]), $peerId))
        ->assertOk();
}

it('offers the pay button to an admin', function () {
    $bot = $this->makeBot();
    $invoice = pendingInvoice($bot);
    viewAs($this->makeBotUser($bot, 1001, ['power' => Roles::ADMIN->value]));

    expect(adminButton($invoice)['callback_data'])->toBe(encodeCallback('ADMIN_PAYMENT', 'pay', [$invoice->id]));
});

it('offers the pay button to the bot owner even without the admin role', function () {
    $bot = $this->makeBot(['bot_owner_peer_id' => 1002]);
    $invoice = pendingInvoice($bot);
    viewAs($this->makeBotUser($bot, 1002));

    expect(adminButton($invoice))->not->toBeNull();
});

it('hides the pay button from a member', function () {
    $bot = $this->makeBot();
    $invoice = pendingInvoice($bot);
    viewAs($this->makeBotUser($bot, 1003));

    expect(adminButton($invoice))->toBeNull();
});

it('pays the invoice in one click and records which admin paid it', function () {
    $bot = $this->makeBot();
    $invoice = pendingInvoice($bot);
    $admin = $this->makeBotUser($bot, 1004, ['power' => Roles::ADMIN->value]);

    pressPay($bot, $invoice, 1004);

    $invoice->refresh();
    $attempt = $invoice->paymentAttempt;

    expect($invoice->status)->toBe('paid')
        ->and($attempt)->toBeInstanceOf(AdminPaymentAttempt::class)
        ->and($attempt->admin->is($admin))->toBeTrue()
        ->and($attempt->status)->toBe('succeed')
        ->and((float) $attempt->amount)->toBe(150000.0);
});

it('lets the bot owner pay without the admin role', function () {
    $bot = $this->makeBot(['bot_owner_peer_id' => 1007]);
    $invoice = pendingInvoice($bot);
    $this->makeBotUser($bot, 1007);

    pressPay($bot, $invoice, 1007);

    expect($invoice->fresh()->status)->toBe('paid');
});

it('refuses a member who replays the callback', function () {
    $bot = $this->makeBot();
    $invoice = pendingInvoice($bot);
    $this->makeBotUser($bot, 1005);

    pressPay($bot, $invoice, 1005);

    expect($invoice->fresh()->status)->toBe('pending')
        ->and(AdminPaymentAttempt::count())->toBe(0);
});

it('does not pay an invoice twice', function () {
    $bot = $this->makeBot();
    $invoice = pendingInvoice($bot);
    $this->makeBotUser($bot, 1006, ['power' => Roles::ADMIN->value]);

    pressPay($bot, $invoice, 1006);
    pressPay($bot, $invoice, 1006);

    expect(AdminPaymentAttempt::count())->toBe(1);
    $this->assertTelegramSent(fn (Request $r) => str_ends_with($r->url(), '/answerCallbackQuery')
        && $r['text'] === __('tbe-gateway-admin::invoice.alerts.already_paid'));
});
