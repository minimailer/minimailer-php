<?php

declare(strict_types=1);

namespace MiniMailer\Exceptions;

/**
 * HTTP 415 — the request was not sent as `application/vnd.api+json`.
 */
final class UnsupportedMediaTypeException extends ApiException {}
