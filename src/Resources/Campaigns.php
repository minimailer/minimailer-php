<?php

declare(strict_types=1);

namespace MiniMailer\Resources;

/**
 * Marketing campaigns — the v1 `campaigns` collection.
 */
final class Campaigns extends Resource
{
    protected function path(): string
    {
        return 'campaigns';
    }
}
