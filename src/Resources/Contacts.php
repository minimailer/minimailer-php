<?php

declare(strict_types=1);

namespace MiniMailer\Resources;

/**
 * Audience contacts — the v1 `contacts` collection.
 */
final class Contacts extends Resource
{
    protected function path(): string
    {
        return 'contacts';
    }
}
