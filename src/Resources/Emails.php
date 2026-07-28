<?php

declare(strict_types=1);

namespace MiniMailer\Resources;

use MiniMailer\Resources\Concerns\CreatesResources;
use MiniMailer\Resources\Concerns\DeletesResources;
use MiniMailer\Resources\Concerns\ListsResources;
use MiniMailer\Resources\Concerns\RetrievesResources;
use MiniMailer\Response;

/**
 * Outbound (transactional) emails — the v1 `outbound-emails` collection.
 * No update: a queued send is immutable; delete removes the stored record only.
 */
final class Emails extends Resource
{
    use CreatesResources;
    use DeletesResources;
    use ListsResources;
    use RetrievesResources;

    protected function path(): string
    {
        return 'outbound-emails';
    }

    public function stats(string $id): Response
    {
        return $this->client->get($this->path(), $id.'/stats');
    }
}
