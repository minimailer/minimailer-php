<?php

declare(strict_types=1);

namespace MiniMailer\Resources;

use Override;

/**
 * API tokens — the v1 `tokens` collection.
 */
final class Tokens extends Resource
{
    #[Override]
    protected function path(): string
    {
        return 'tokens';
    }
}
