<?php

declare(strict_types=1);

namespace MiniMailer\Resources\Concerns;

use MiniMailer\Response;

/**
 * `GET /{collection}` — list the resource collection.
 */
trait ListsResources
{
    /**
     * @param  array<string, mixed>  $query
     */
    public function list(array $query = []): Response
    {
        return $this->client->list($this->path(), $query);
    }
}
