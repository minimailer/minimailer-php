<?php

declare(strict_types=1);

namespace MiniMailer\Exceptions;

/**
 * HTTP 409 — an idempotency key was reused with a different payload.
 */
final class ConflictException extends ApiException {}
