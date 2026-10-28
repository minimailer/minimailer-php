# MiniMailer PHP SDK

[![Latest release](https://img.shields.io/github/v/release/minimailer/minimailer-php?style=flat-square)](https://github.com/minimailer/minimailer-php/releases)
[![License](https://img.shields.io/github/license/minimailer/minimailer-php?style=flat-square&color=yellow)](https://github.com/minimailer/minimailer-php/blob/main/LICENSE)
[![Tests](https://img.shields.io/github/actions/workflow/status/minimailer/minimailer-php/tests.yml?branch=main&label=tests&style=flat-square)](https://github.com/minimailer/minimailer-php/actions/workflows/tests.yml)

A lightweight, framework-agnostic PHP client for the [MiniMailer](https://minimailer.app) API — EU-resident, GDPR-first email infrastructure.

> Pre-release (`0.x`), pinned to the MiniMailer **v1** API contract. The public surface is not covered by SemVer guarantees until `1.0.0`; every change is listed in the [changelog](CHANGELOG.md).

## Requirements

- PHP 8.2+
- Guzzle 7.15+ or 8.0.1+ ships as the default HTTP client. Any PSR-18 client can be injected instead (see [HTTP client behaviour](#http-client-behaviour)).

## Installation

The package is not on Packagist yet. Add the repository, then require it:

```bash
composer config repositories.minimailer-php vcs https://github.com/minimailer/minimailer-php
composer require minimailer/minimailer-php:^0.1
```

## Usage

```php
use MiniMailer\MiniMailer;

$mailer = MiniMailer::make('your-api-key');

$response = $mailer->emails()->create([
    'data' => [
        'type' => 'outbound-emails',
        'attributes' => [
            'from' => 'you@your-domain.com',
            'to' => 'customer@example.com',
            'subject' => 'Welcome aboard',
            'html' => '<p>Thanks for signing up.</p>',
        ],
        'relationships' => [
            'domain' => ['data' => ['type' => 'domains', 'id' => 'dom_...']],
        ],
    ],
]);

$id = $response->json('data.id'); // email_01JZX...
```

### Available resources

Each resource exposes **exactly** the operations the v1 API supports — a verb the API does not offer does not exist on the SDK:

| Accessor | Operations |
| --- | --- |
| `emails()` | `list`, `get`, `create`, `delete`, `stats($id)` |
| `domains()` | `list`, `get`, `create`, `update`, `delete`, `records($id)` (DNS records to publish; verification is automatic) |
| `webhooks()` | `list`, `get`, `create`, `update`, `delete`, `deliveries($id)` |
| `deliveries()` | `get`, `replay($id)` (bodyless POST, 202) |
| `inbound()` | `list`, `get` (read-only) |
| `inboxes()` | `list`, `get`, `create`, `update`, `delete`, `inboundEmails($id)` |
| `audiences()` | `list`, `get`, `create`, `update`, `delete`, `contacts($id)`, `addContact($id, $data)`, `importContacts($id, $data)` |
| `contacts()` | `get`, `update`, `delete` (created through an audience) |
| `campaigns()` | `list`, `get`, `create`, `update`, `delete`, `stats($id)` |
| `suppressions()` | `list`, `delete` (permanent entries return 422) |
| `tokens()` | `create`, `delete` (plaintext token returned once on create) |
| `recipients()` | `forget($email)`, `data($email)` (GDPR erasure / subject access, async 202) |
| `compliance()` | `createConsent($data)`, `withdrawConsent($data)` (GDPR Art. 7(3)), `controllerAudit()` |

### Error handling

Every non-2xx response throws a typed exception extending `MiniMailer\Exceptions\ApiException`:

| Status | Exception |
| --- | --- |
| 3xx | `ApiException` (redirects are never followed — see below) |
| 401 | `AuthenticationException` |
| 402 | `PaymentRequiredException` (free-tier monthly sending allowance exhausted) |
| 403 | `AuthorizationException` (missing scope, or a JSON:API protocol rule) |
| 404 | `NotFoundException` (see below) |
| 409 | `ConflictException` |
| 415 | `UnsupportedMediaTypeException` |
| 422 | `ValidationException` (see `->errors()`) |
| 429 | `RateLimitException` (see `->retryAfter()`) |

Every exception carries the response (`->response`) and the JSON:API error code (`->errorCode()`, e.g. `not_found`, `missing_scope`, `agent_throttled`). Branch on the status and that code; the message is the error's `detail`, whose wording is not part of the contract.

A `404` means the record does not exist **or** belongs to another account. The API answers both cases with the same status, code and detail by design, so a `NotFoundException` never confirms that an identifier is in use, and a `403` is never returned for another account's record.

```php
use MiniMailer\Exceptions\ValidationException;

try {
    $mailer->emails()->create($payload);
} catch (ValidationException $e) {
    foreach ($e->errors() as $error) {
        echo $error['detail'], PHP_EOL;
    }
}
```

### HTTP client behaviour

When no client is injected, the SDK builds a Guzzle client that:

- gives up after **10 s** connecting and **30 s** per request (Guzzle's own default would wait forever),
- **never follows redirects** — a `3xx` response surfaces as an `ApiException` instead of replaying your bearer token against another host,
- identifies itself with `User-Agent: minimailer-php/<installed version>`.

Every request also carries `Authorization: Bearer …` and the JSON:API media type. Headers you pass to `create()` / `update()` override those defaults, case-insensitively.

To tune any of this, inject your own PSR-18 client:

```php
use GuzzleHttp\Client as GuzzleClient;
use MiniMailer\Client;
use MiniMailer\MiniMailer;

$http = new GuzzleClient(['timeout' => 5.0, 'connect_timeout' => 2.0]);

$mailer = new MiniMailer(new Client('your-api-key', 'https://api.minimailer.app/', $http));
```

## API reference

The v1 contract is published as an OpenAPI document: <https://api.minimailer.app/openapi.yaml>. Every accessor above maps one-to-one onto an operation in it.

## Laravel

For a first-class Laravel mail transport (`MAIL_MAILER=minimailer`), use the companion package [`minimailer/laravel-minimailer`](https://github.com/minimailer/laravel-minimailer), which builds on this SDK.

## Contributing and security

- [Contributing guide](.github/CONTRIBUTING.md) — setup, tests, and the no-network rule for the test suite.
- [Security policy](.github/SECURITY.md) — report vulnerabilities privately, never in a public issue.
- [Changelog](CHANGELOG.md)

## License

[MIT](LICENSE).
