<?php

declare(strict_types=1);

namespace MiniMailer\Exceptions;

/**
 * HTTP 429 — too many requests. Branch on {@see errorCode()}: `rate_limited`
 * (the account exceeded its API rate limit, or the free tier's daily sending
 * allowance is exhausted) or `agent_throttled` (the agent named by `agent_id`
 * has a restricted sender reputation and has exhausted its send allowance).
 * Inspect {@see retryAfter()} to back off.
 */
final class RateLimitException extends ApiException
{
    public function retryAfter(): ?int
    {
        $value = $this->response?->header('Retry-After');

        return $value !== null && is_numeric($value) ? (int) $value : null;
    }
}
