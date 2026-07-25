# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Commands

```bash
composer test                       # Pest suite (alias for `pest`)
composer lint                       # Pint in check mode — never mutates
vendor/bin/pint                     # fix formatting locally
vendor/bin/pest tests/Unit/EmailsTest.php          # single file
vendor/bin/pest --filter="typed exceptions"        # single test by name
```

Two workflows, both installing with `composer update` rather than `install` — `composer.lock` is gitignored (library convention):

- **`tests.yml`** — `pest` across a 24-cell matrix: PHP 8.2–8.5 × ubuntu/windows/macos × `prefer-lowest`/`prefer-stable`. Run `composer update --prefer-lowest` locally before pushing a dependency change; the lowest-bound cell is what catches widened constraints.
- **`quality.yml`** — `composer lint` (Pint) on a single cell, PHP 8.4 / ubuntu.

CI **verifies, never mutates**: `composer lint` is `pint --test`, which fails the run on a style violation instead of fixing it and committing back. Both workflows hold a `contents: read` token. Fix style locally with `vendor/bin/pint` and push the fix — never convert these into auto-commit steps.

### Workflow actions must be pinned to a full-length commit SHA

Every `uses:` in `.github/workflows/` is pinned to a full 40-character commit SHA with the human-readable version in a trailing comment — never a tag or branch ref, which are mutable and let an upstream repo change what runs in CI:

```yaml
uses: actions/checkout@11d5960a326750d5838078e36cf38b85af677262 # v4.4.0
```

This applies to new steps and to version bumps. Resolve the SHA from the tag rather than copying it from a README, and confirm the comment matches what you pinned:

```bash
gh api repos/OWNER/REPO/git/ref/tags/TAG --jq '.object.sha,.object.type'
```

Expect `type: commit`. If it returns `tag`, the ref is annotated — dereference it (`gh api repos/OWNER/REPO/git/tags/SHA --jq '.object.sha'`) and pin the commit it points at, not the tag object.

## Architecture

Framework-agnostic SDK for the MiniMailer **v1 JSON:API**. Four layers, each with one job:

1. **`MiniMailer`** (`src/MiniMailer.php`) — entry point. `MiniMailer::make($apiKey)` wires a default `Client`; the constructor takes a `ClientContract` so a framework container (e.g. `minimailer/laravel-minimailer`) can inject its own. Every accessor (`emails()`, `domains()`, …) returns a fresh resource bound to the same client.
2. **`Client`** (`src/Client.php`) — the only place HTTP happens. Builds a PSR-7 `Request`, sends via any PSR-18 `ClientInterface` (Guzzle by default), stamps `Authorization: Bearer`, `Accept`/`Content-Type: application/vnd.api+json` on every call, and maps status → typed exception in `throwForStatus()`.
3. **`Resources\Resource`** (`src/Resources/`) — abstract base giving `list/get/create/update/delete`. Subclasses declare only `path()` (the v1 collection name) plus resource-specific actions. Note the path names differ from the accessors: `emails()` → `outbound-emails`, `inbound()` → `inbound-emails`.
4. **`Response`** (`src/Response.php`) — wraps the PSR-7 response so no concrete HTTP client type leaks across the public surface. `json('data.id')` supports dot-notation (including numeric segments, e.g. `errors.0.detail`); body is read once and memoised, rewinding a seekable stream first.

### Invariants worth preserving

- **No concrete HTTP type in the public API.** Resources and exceptions expose `MiniMailer\Response`, never a Guzzle/PSR-7 response. `Response::toPsr()` is the deliberate escape hatch.
- **Exception mapping lives in one `match`** (`Client::throwForStatus`). Every subclass of `ApiException` maps to exactly one status: 401/403/409/415/422/429; anything else ≥400 falls through to `ApiException`. Adding a status means adding a class *and* an arm. The message prefers `errors.0.detail` from the JSON:API body, falling back to the raw body.
- **PHP 8.2 floor.** No `#[Override]`, no 8.3+ syntax — the matrix will catch it, but `readonly class` (8.2) is fine and used on `Client`/`MiniMailer`.
- **Non-CRUD actions go on the resource, expressed via `ClientContract`**, not by reaching for HTTP directly: see `Emails::stats()` (`get(path, "$id/stats")`) and `Domains::verify()` (`create("path/$id/verify")` → bodyless POST).

## Tests

Pest, unit-only, no network. `fakeMiniMailer([$responses])` in `tests/Pest.php` builds a client backed by Guzzle's `MockHandler`. When an assertion needs the *outgoing* request (path, headers, encoded body), build the stack inline with `Middleware::history($history)` instead — `EmailsTest` is the pattern to copy. `phpunit.xml` sets `failOnWarning` and `failOnRisky`.

## Context

Private pre-release under the `minimailer/*` org, pinned to the v1 API contract. The companion package `minimailer/laravel-minimailer` (a Symfony mail transport) depends on this SDK and path-links it during the private phase — a breaking change here breaks that package.
