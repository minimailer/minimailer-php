<?php

declare(strict_types=1);

namespace MiniMailer\Resources;

use Override;

/**
 * Inbound (received) emails — the v1 `inbound-emails` collection. Read-only.
 */
final class Inbound extends Resource
{
    #[Override]
    protected function path(): string
    {
        return 'inbound-emails';
    }
}
