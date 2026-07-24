<?php

declare(strict_types=1);

namespace MiniMailer\Exceptions;

use MiniMailer\Response;
use Throwable;

/**
 * Thrown when the API returns an error response (or the request could not be
 * dispatched). Carries the underlying {@see Response} when one exists.
 */
class ApiException extends MiniMailerException
{
    public function __construct(
        public readonly ?Response $response = null,
        string $message = '',
        int $code = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct(
            $message !== '' ? $message : ($response?->body() ?? ''),
            $code !== 0 ? $code : ($response?->status() ?? 0),
            $previous,
        );
    }
}
