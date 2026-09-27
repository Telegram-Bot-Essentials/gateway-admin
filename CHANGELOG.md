# Changelog

All notable changes to this project are documented here. Format follows
[Keep a Changelog](https://keepachangelog.com/en/1.0.0/); versioning is
[Semantic Versioning](https://semver.org/), with the common `telegram-bot-essentials/*`
convention that a 0.x minor may carry breaking changes until the package's API stabilizes at 1.0.

## [Unreleased]

### Added

- `👑 Pay as admin` gateway: a button on every invoice, shown only to admins, the bot
  owner and the developer, that marks the invoice paid in one click.
- `AdminPaymentAttempt` recording which admin paid an invoice and for how much.
- An audit log entry per admin payment.
