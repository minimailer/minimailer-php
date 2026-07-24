<?php

declare(strict_types=1);

namespace MiniMailer\Exceptions;

/**
 * HTTP 401 — the API key is missing, malformed, or invalid.
 */
final class AuthenticationException extends ApiException {}
