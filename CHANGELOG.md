# Changelog

All notable changes to `minimailer/minimailer-php` will be documented in this file.

## Unreleased

Pre-release. The package is pinned to the MiniMailer **v1** API contract; the public
surface is not yet covered by SemVer guarantees until `1.0.0` is tagged.

### Added

- `Compliance::withdrawConsent()` — `POST /recipient-consents/withdraw` (GDPR
  Art. 7(3)); the consent resource now carries `withdrawn_at`/`withdrawn_via`.

### Fixed

- Nested-collection URLs no longer use the retired `/relationships/` segment:
  `audiences/{id}/contacts`, `audiences/{id}/contacts/import`,
  `domains/{id}/records`, `inboxes/{id}/inbound-emails` and
  `webhooks/{id}/deliveries` now match the v1 contract (the old URLs 404
  against the live API).

### Changed (breaking)

- Each resource now exposes **exactly** the operations its v1 collection supports,
  composed from per-verb traits. Removed methods that had no endpoint:
  `Domains::verify()` (verification is automatic DNS polling — see
  `Domains::records()`), `update()` on emails, `list()`/`get()`/`update()` on
  tokens, `create()`/`get()`/`update()` on suppressions, `list()`/`create()` on
  contacts, and all write methods on inbound emails.

### Added

- `Domains::records()`, `Webhooks::deliveries()`, `Inboxes::inboundEmails()`,
  `Campaigns::stats()`, `Audiences::contacts()`/`addContact()`/`importContacts()`.
- New resources: `deliveries()` (`get`, `replay`), `recipients()` (GDPR
  `forget`/`data`, async 202), `compliance()` (`createConsent`,
  `controllerAudit`).
- Complete mocked test suite asserting the verb, URL, status and body shape of
  every v1 operation, plus a reflection-based surface-parity test.

### Fixed

- Every path parameter is now URL-encoded at the resource layer, and query
  strings are built with an explicit `&` separator and RFC 3986 encoding.
