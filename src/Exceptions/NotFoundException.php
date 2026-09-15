<?php

declare(strict_types=1);

namespace MiniMailer\Exceptions;

/**
 * HTTP 404 — the resource does not exist, or it belongs to another account.
 *
 * The API answers both cases with the same status, error code (`not_found`)
 * and detail by design, so this exception never confirms that an identifier
 * is in use. Do not inspect the message to tell the two cases apart: it is
 * identical for both, and the API is built so that it stays that way.
 */
final class NotFoundException extends ApiException {}
