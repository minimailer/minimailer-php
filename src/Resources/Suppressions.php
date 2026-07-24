<?php

declare(strict_types=1);

namespace MiniMailer\Resources;

/**
 * Suppression list — the v1 `suppressions` collection.
 */
final class Suppressions extends Resource
{
    protected function path(): string
    {
        return 'suppressions';
    }
}
