<?php

declare(strict_types=1);

namespace MiniMailer\Resources;

/**
 * API tokens — the v1 `tokens` collection.
 */
final class Tokens extends Resource
{
    protected function path(): string
    {
        return 'tokens';
    }
}
