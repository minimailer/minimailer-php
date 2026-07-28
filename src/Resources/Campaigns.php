<?php

declare(strict_types=1);

namespace MiniMailer\Resources;

use MiniMailer\Resources\Concerns\CreatesResources;
use MiniMailer\Resources\Concerns\DeletesResources;
use MiniMailer\Resources\Concerns\ListsResources;
use MiniMailer\Resources\Concerns\RetrievesResources;
use MiniMailer\Resources\Concerns\UpdatesResources;
use MiniMailer\Response;

/**
 * Marketing campaigns — the v1 `campaigns` collection. The lifecycle state
 * machine (schedule, pause, cancel) is driven through `update()`.
 */
final class Campaigns extends Resource
{
    use CreatesResources;
    use DeletesResources;
    use ListsResources;
    use RetrievesResources;
    use UpdatesResources;

    protected function path(): string
    {
        return 'campaigns';
    }

    public function stats(string $id): Response
    {
        return $this->client->get($this->path(), rawurlencode($id).'/stats');
    }
}
