<?php

declare(strict_types=1);

namespace MiniMailer\Resources;

use Override;

/**
 * Suppression list — the v1 `suppressions` collection.
 */
final class Suppressions extends Resource
{
    #[Override]
    protected function path(): string
    {
        return 'suppressions';
    }
}
