<?php

declare(strict_types=1);

namespace MiniMailer\Exceptions;

/**
 * HTTP 409 — the request conflicts with the endpoint or the current state.
 * Branch on {@see errorCode()}: `conflict` (the document's resource type does
 * not match the endpoint, its id does not match the resource being updated,
 * or an idempotency key was reused with a different payload) or
 * `agent_category_conflict` (the `agent_id` names an identity already bound
 * to the other kind of mail — transactional or marketing, never both).
 */
final class ConflictException extends ApiException {}
