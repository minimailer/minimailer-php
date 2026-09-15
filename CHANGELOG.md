# Changelog

All notable changes to `minimailer/minimailer-php` will be documented in this file.

## Unreleased

Pre-release. The package is pinned to the MiniMailer **v1** API contract; the public
surface is not yet covered by SemVer guarantees until `1.0.0` is tagged.

### Added

- `NotFoundException` (404) and `PaymentRequiredException` (402), mapped like
  the other typed exceptions. A 404 means the record does not exist or belongs
  to another account; the API returns the same status, code and detail for
  both by design, so the exception never confirms that an identifier is in use.
- `ApiException::errorCode()` — the JSON:API error code (`errors.0.code`), the
  value the contract says to branch on (`not_found`, `missing_scope`,
  `agent_throttled`, …); the message remains the error's `detail`.
- `Compliance::withdrawConsent()` — `POST /recipient-consents/withdraw` (GDPR
  Art. 7(3)); the consent resource now carries `withdrawn_at`/`withdrawn_via`.

### Changed

- `AuthorizationException` (403) now documents what the API actually returns
  it for: a missing scope or a JSON:API protocol rule. It is never thrown for
  another account's record (404) nor for an exhausted sending allowance
  (402 monthly, 429 daily), which the previous docblock attributed to 403.

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
