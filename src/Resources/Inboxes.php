<?php

declare(strict_types=1);

namespace MiniMailer\Resources;

use Override;

/**
 * Inboxes — the v1 `inboxes` collection.
 */
final class Inboxes extends Resource
{
    #[Override]
    protected function path(): string
    {
        return 'inboxes';
    }
}
