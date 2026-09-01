<?php

declare(strict_types=1);

namespace MiniMailer\Resources;

use MiniMailer\Resources\Concerns\DeletesResources;
use MiniMailer\Resources\Concerns\ListsResources;

/**
 * Suppression list — the v1 `suppressions` collection. Entries are created by
 * the platform (bounces, complaints, unsubscribes), never via the API; delete
 * removes reversible entries only (permanent ones return 422). Each entry
 * carries a `scope` (`marketing` or `all`; filterable via `filter[scope]`) —
 * only `all`-scope entries block transactional sends.
 */
final class Suppressions extends Resource
{
    use DeletesResources;
    use ListsResources;

    protected function path(): string
    {
        return 'suppressions';
    }
}
