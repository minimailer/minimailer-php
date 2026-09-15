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

Three workflows, all installing with `composer update` rather than `install` — `composer.lock` is gitignored (library convention):

- **`tests.yml`** — `pest` across a 24-cell matrix: PHP 8.2–8.5 × ubuntu/windows/macos × `prefer-lowest`/`prefer-stable`. Run `composer update --prefer-lowest` locally before pushing a dependency change; the lowest-bound cell is what catches widened constraints.
- **`quality.yml`** — `composer lint` (Pint) on a single cell, PHP 8.4 / ubuntu.
- **`composer-security.yml`** — `composer audit` on a pinned `composer:2.10`, which fails on malware and advisories. Triggers on `composer.json` changes, PRs, and a weekly cron; the cron is the point, since a dependency can be flagged long after the constraint last changed. `managed-by: ComposerSecurityStamp` — regenerate it rather than hand-editing, but note the pins below were aligned by hand after stamping.

CI **verifies, never mutates**: `composer lint` is `pint --test`, which fails the run on a style violation instead of fixing it and committing back. Every workflow holds a `contents: read` token and sets `persist-credentials: false` on checkout so the job never keeps a usable credential in `.git/config`. Fix style locally with `vendor/bin/pint` and push the fix — never convert these into auto-commit steps.

Dependabot (`.github/dependabot.yml`) watches `github-actions` and `composer` weekly. Its job on the actions side is keeping the SHA pins from rotting — that is the intended way pins get bumped, so review those PRs rather than bumping by hand.

### Packaging

`.gitattributes` `export-ignore` decides what a consumer actually downloads: only `src/`, `README.md`, `LICENSE`, `composer.json` and `CHANGELOG.md` ship. Adding a new dev-only directory or config file means adding an `export-ignore` line for it. Verify with:

```bash
git archive --worktree-attributes --format=tar HEAD | tar -t
```

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

Resolve against a **precise** release tag (`v7.0.1`), never a rolling major (`v6`, `v2`). A rolling tag moves, so a comment reading `# v6` silently becomes wrong about what is pinned — which is exactly what happened to the pin this repo inherited. All three workflows pin the same two actions at the same two SHAs; keep it that way so a bump is one decision, not three.

## Architecture

Framework-agnostic SDK for the MiniMailer **v1 JSON:API**. Four layers, each with one job:

1. **`MiniMailer`** (`src/MiniMailer.php`) — entry point. `MiniMailer::make($apiKey)` wires a default `Client`; the constructor takes a `ClientContract` so a framework container (e.g. `minimailer/laravel-minimailer`) can inject its own. Every accessor (`emails()`, `domains()`, …) returns a fresh resource bound to the same client.
2. **`Client`** (`src/Client.php`) — the only place HTTP happens. Builds a PSR-7 `Request`, sends via any PSR-18 `ClientInterface` (Guzzle by default), stamps `Authorization: Bearer`, `Accept`/`Content-Type: application/vnd.api+json` on every call, and maps status → typed exception in `throwForStatus()`.
3. **`Resources\Resource`** (`src/Resources/`) — abstract base holding only the client and `path()` (the v1 collection name). CRUD verbs live in one-method traits under `Resources/Concerns/` (`ListsResources`, `RetrievesResources`, `CreatesResources`, `UpdatesResources`, `DeletesResources`); each resource composes **exactly** the verbs its v1 collection supports, so an unsupported verb is a type error, not a runtime 404. `SurfaceParityTest` pins the composition — changing a resource's surface means updating the spec mapping there too. Note the path names differ from the accessors: `emails()` → `outbound-emails`, `inbound()` → `inbound-emails`.
4. **`Response`** (`src/Response.php`) — wraps the PSR-7 response so no concrete HTTP client type leaks across the public surface. `json('data.id')` supports dot-notation (including numeric segments, e.g. `errors.0.detail`); body is read once and memoised, rewinding a seekable stream first.

### Invariants worth preserving

- **No concrete HTTP type in the public API.** Resources and exceptions expose `MiniMailer\Response`, never a Guzzle/PSR-7 response. `Response::toPsr()` is the deliberate escape hatch.
- **Exception mapping lives in one `match`** (`Client::throwForStatus`). Every subclass of `ApiException` maps to exactly one status: 401/402/403/404/409/415/422/429; anything else ≥400 falls through to `ApiException`. Adding a status means adding a class *and* an arm. The message prefers `errors.0.detail` from the JSON:API body, falling back to the raw body; `errorCode()` exposes `errors.0.code`, which is what the contract says to branch on.
- **The 404 posture is part of the public surface.** `NotFoundException` covers both "does not exist" and "belongs to another account"; the API returns one identical body for both by design, and `ExceptionMappingTest` pins that the SDK surfaces nothing that differs between the two. Never add logic or wording that tries to tell them apart, and never document 403 as an ownership or billing signal (ownership is 404, billing is 402/429).
- **PHP 8.2 floor.** No `#[Override]`, no 8.3+ syntax — the matrix will catch it, but `readonly class` (8.2) is fine and used on `Client`/`MiniMailer`.
- **Non-CRUD actions go on the resource, expressed via `ClientContract`**, not by reaching for HTTP directly: see `Emails::stats()` (`get(path, "$id/stats")`) and `Deliveries::replay()` (`create("path/$id/replay")` → bodyless POST, 202).
- **Every path parameter is `rawurlencode()`d at the resource layer** — the verb traits encode `$id`, action methods encode it before appending their suffix (`rawurlencode($id).'/stats'`), and `Recipients` encodes the email address. `Client` concatenates without encoding by design; never pass unencoded user input through it.

## Tests

Pest, unit-only, **never any network** — every client is backed by Guzzle's `MockHandler`, and `NoNetworkTest` fails the suite if any test constructs an unmocked Guzzle client. Two helpers in `tests/Pest.php`: `fakeMiniMailer([$responses])` for response-side assertions, and `fakeMiniMailerWithHistory([$responses], $history)` when the assertion needs the *outgoing* request (method, URL, headers, encoded body) — `EmailsTest` is the pattern to copy. One contract-test file per resource asserts the exact verb/URL/status of every v1 operation. `phpunit.xml` sets `failOnWarning` and `failOnRisky`.

## Context

Private pre-release under the `minimailer/*` org, pinned to the v1 API contract. The companion package `minimailer/laravel-minimailer` (a Symfony mail transport) depends on this SDK and path-links it during the private phase — a breaking change here breaks that package.
