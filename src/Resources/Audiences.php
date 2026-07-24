<?php

declare(strict_types=1);

namespace MiniMailer\Resources;

use Override;

/**
 * Marketing audiences — the v1 `audiences` collection.
 */
final class Audiences extends Resource
{
    #[Override]
    protected function path(): string
    {
        return 'audiences';
    }
}
