# MiniMailer PHP SDK

A lightweight, framework-agnostic PHP client for the [MiniMailer](https://minimailer.app) API — EU-resident, GDPR-first email infrastructure.

> Private / pre-release. Pinned to the MiniMailer **v1** API contract.

## Requirements

- PHP 8.2+
- A PSR-18 HTTP client (Guzzle is used by default)

## Installation

```bash
composer require minimailer/minimailer-php
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
            'domain' => ['type' => 'domains', 'id' => 'dom_...'],
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
| 401 | `AuthenticationException` |
| 403 | `AuthorizationException` |
| 409 | `ConflictException` |
| 415 | `UnsupportedMediaTypeException` |
| 422 | `ValidationException` (see `->errors()`) |
| 429 | `RateLimitException` (see `->retryAfter()`) |

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

## Laravel

For a first-class Laravel mail transport (`MAIL_MAILER=minimailer`), use the companion package `minimailer/laravel-minimailer`, which builds on this SDK.

## License

MIT.
