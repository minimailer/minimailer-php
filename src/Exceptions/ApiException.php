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

    /**
     * The JSON:API error code (`errors.0.code`, e.g. `not_found`,
     * `missing_scope`, `agent_throttled`). The contract says to branch on the
     * status and this code, never on the detail wording carried as the
     * message. Null when there is no response or the body is not a JSON:API
     * error document.
     */
    public function errorCode(): ?string
    {
        $code = $this->response?->json('errors.0.code');

        return is_string($code) && $code !== '' ? $code : null;
    }
}
