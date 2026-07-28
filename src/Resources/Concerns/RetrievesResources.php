<?php

declare(strict_types=1);

namespace MiniMailer\Resources\Concerns;

use MiniMailer\Response;

/**
 * `GET /{collection}/{id}` — retrieve a single resource.
 */
trait RetrievesResources
{
    public function get(string $id): Response
    {
        return $this->client->get($this->path(), rawurlencode($id));
    }
}
