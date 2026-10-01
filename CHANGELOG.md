# Changelog

All notable changes to this project are documented here. Format follows
[Keep a Changelog](https://keepachangelog.com/en/1.0.0/); versioning is
[Semantic Versioning](https://semver.org/), with the common `telegram-bot-essentials/*`
convention that a 0.x minor may carry breaking changes until the package's API stabilizes at 1.0.

## [Unreleased]

### Changed

- Reworded the user-facing English and Persian strings to read more naturally; no keys or placeholders changed.

## [0.0.2] - 2026-09-28

### Changed

- Accepts essence 0.16 alongside 0.15.

## [0.0.1] - 2026-09-27

### Added

- `👑 Pay as admin` gateway: a button on every invoice, shown only to admins, the bot
  owner and the developer, that marks the invoice paid in one click.
- `AdminPaymentAttempt` recording which admin paid an invoice and for how much.
- An audit log entry per admin payment.
