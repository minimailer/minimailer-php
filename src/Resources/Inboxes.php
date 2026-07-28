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
 * Inboxes — the v1 `inboxes` collection.
 */
final class Inboxes extends Resource
{
    use CreatesResources;
    use DeletesResources;
    use ListsResources;
    use RetrievesResources;
    use UpdatesResources;

    protected function path(): string
    {
        return 'inboxes';
    }

    /**
     * Inbound emails received by one inbox.
     *
     * @param  array<string, mixed>  $query
     */
    public function inboundEmails(string $id, array $query = []): Response
    {
        return $this->client->list($this->path().'/'.rawurlencode($id).'/relationships/inbound-emails', $query);
    }
}
