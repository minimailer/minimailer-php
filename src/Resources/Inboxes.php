<?php

declare(strict_types=1);

namespace MiniMailer\Resources;

/**
 * Inboxes — the v1 `inboxes` collection.
 */
final class Inboxes extends Resource
{
    protected function path(): string
    {
        return 'inboxes';
    }
}
