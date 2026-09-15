<?php

declare(strict_types=1);

namespace MiniMailer\Exceptions;

/**
 * HTTP 402 — the free tier's monthly sending allowance is exhausted. Upgrade
 * to a paid plan to keep sending; inbound mail is always received. (Exhausting
 * the free tier's daily allowance is a 429 — see {@see RateLimitException}.)
 */
final class PaymentRequiredException extends ApiException {}
