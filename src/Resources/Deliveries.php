<?php

declare(strict_types=1);

namespace MiniMailer\Resources;

use MiniMailer\Resources\Concerns\RetrievesResources;
use MiniMailer\Response;

/**
 * Webhook delivery attempts — the v1 `deliveries` collection. List them per
 * webhook via {@see Webhooks::deliveries()}.
 */
final class Deliveries extends Resource
{
    use RetrievesResources;

    protected function path(): string
    {
        return 'deliveries';
    }

    /**
     * Re-send a past delivery with a fresh signature (bodyless POST, 202).
     */
    public function replay(string $id): Response
    {
        return $this->client->create($this->path().'/'.rawurlencode($id).'/replay');
    }
}
