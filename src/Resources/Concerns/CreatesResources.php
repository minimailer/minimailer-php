<?php

declare(strict_types=1);

namespace MiniMailer\Resources\Concerns;

use MiniMailer\Response;

/**
 * `POST /{collection}` — create a resource.
 */
trait CreatesResources
{
    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, string>  $headers
     */
    public function create(array $data, array $headers = []): Response
    {
        return $this->client->create($this->path(), $data, $headers);
    }
}
