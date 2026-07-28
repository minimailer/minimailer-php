<?php

declare(strict_types=1);

namespace MiniMailer\Resources;

use MiniMailer\Resources\Concerns\ListsResources;
use MiniMailer\Resources\Concerns\RetrievesResources;

/**
 * Inbound (received) emails — the v1 `inbound-emails` collection. Read-only.
 */
final class Inbound extends Resource
{
    use ListsResources;
    use RetrievesResources;

    protected function path(): string
    {
        return 'inbound-emails';
    }
}
