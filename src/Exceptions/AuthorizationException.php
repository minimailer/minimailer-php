<?php

declare(strict_types=1);

namespace MiniMailer\Exceptions;

/**
 * HTTP 403 — the token lacks the required scope, or the account has no active
 * subscription for the requested action.
 */
final class AuthorizationException extends ApiException {}
