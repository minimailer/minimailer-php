<?php

declare(strict_types=1);

namespace MiniMailer\Resources;

/**
 * Marketing audiences — the v1 `audiences` collection.
 */
final class Audiences extends Resource
{
    protected function path(): string
    {
        return 'audiences';
    }
}
