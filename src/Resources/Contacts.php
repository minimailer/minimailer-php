<?php

declare(strict_types=1);

namespace MiniMailer\Resources;

use Override;

/**
 * Audience contacts — the v1 `contacts` collection.
 */
final class Contacts extends Resource
{
    #[Override]
    protected function path(): string
    {
        return 'contacts';
    }
}
