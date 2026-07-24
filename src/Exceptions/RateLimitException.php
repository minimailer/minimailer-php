<?php

declare(strict_types=1);

namespace MiniMailer\Exceptions;

/**
 * HTTP 429 — too many requests. Inspect the `Retry-After` header on the
 * carried response to back off.
 */
final class RateLimitException extends ApiException
{
    public function retryAfter(): ?int
    {
        $value = $this->response?->header('Retry-After');

        return $value !== null && is_numeric($value) ? (int) $value : null;
    }
}
