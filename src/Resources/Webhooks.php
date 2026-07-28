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
 * Webhook endpoints — the v1 `webhooks` collection.
 */
final class Webhooks extends Resource
{
    use CreatesResources;
    use DeletesResources;
    use ListsResources;
    use RetrievesResources;
    use UpdatesResources;

    protected function path(): string
    {
        return 'webhooks';
    }

    /**
     * Delivery attempts for a webhook; single deliveries live on
     * {@see Deliveries}.
     *
     * @param  array<string, mixed>  $query
     */
    public function deliveries(string $id, array $query = []): Response
    {
        return $this->client->list($this->path().'/'.rawurlencode($id).'/relationships/deliveries', $query);
    }
}
