# Telegram Bot Essentials — Gateway: Admin

A one-click "pay as admin" gateway for
[`telegram-bot-essentials/billing`](https://github.com/Telegram-Bot-Essentials/billing).
Every invoice gets a `👑 Pay as admin` button that only admins, the bot owner and the
developer see (essence's `hasAccess()`). Pressing it settles the invoice at once: no money
moves, the invoice is marked paid through the normal `InvoicePaid` flow, and an
`AdminPaymentAttempt` records which admin paid it and for how much.

Use it to grant a service for free, compensate a member, or settle an invoice paid outside
the bot.

## Installation

```bash
composer require telegram-bot-essentials/gateway-admin
php artisan migrate
```

No config and no settings: the provider registers the gateway and its callback query.

## How it behaves

- The button shows on every invoice an admin views, including their own purchases, next to
  every other gateway.
- A double tap pays once: the invoice row is locked and an already-paid invoice answers
  "This invoice is already paid." instead.
- Each payment is written to the audit log (`Paid invoice #12 of 150000 as admin`).
- A consuming app that refunds orders can tell an admin-paid invoice by its payment attempt
  (`$invoice->paymentAttempt instanceof AdminPaymentAttempt`) and skip crediting money that
  was never received.

## Testing

```bash
composer test
composer lint
composer analyse
```
