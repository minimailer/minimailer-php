<?php

declare(strict_types=1);

namespace MiniMailer\Exceptions;

/**
 * HTTP 403 — the token lacks the scope the operation requires (error code
 * `missing_scope`), or the request breaks a JSON:API protocol rule
 * (`client_generated_id`, `unsupported_relationship_update`).
 *
 * Never thrown for a record that belongs to another account — that case is a
 * 404 ({@see NotFoundException}) — and never for a billing limit, which is a
 * 402 ({@see PaymentRequiredException}) or a 429 ({@see RateLimitException}).
 */
final class AuthorizationException extends ApiException {}
