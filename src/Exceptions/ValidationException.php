<?php

declare(strict_types=1);

namespace MiniMailer\Exceptions;

/**
 * HTTP 422 — the request failed validation (or hit the suppression list).
 */
final class ValidationException extends ApiException
{
    /**
     * The JSON:API error objects, each with `status`, `code`, `detail` and an
     * optional `source.pointer` to the offending field.
     *
     * @return array<int, array<string, mixed>>
     */
    public function errors(): array
    {
        $errors = $this->response?->json('errors');

        return is_array($errors) ? $errors : [];
    }
}
