<?php

declare(strict_types=1);

namespace MiniMailer\Resources;

use Override;

/**
 * Marketing campaigns — the v1 `campaigns` collection.
 */
final class Campaigns extends Resource
{
    #[Override]
    protected function path(): string
    {
        return 'campaigns';
    }
}
