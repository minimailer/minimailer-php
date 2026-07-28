<?php

declare(strict_types=1);

namespace MiniMailer\Resources\Concerns;

use MiniMailer\Response;

/**
 * `PATCH /{collection}/{id}` — update a resource.
 */
trait UpdatesResources
{
    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, string>  $headers
     */
    public function update(string $id, array $data, array $headers = []): Response
    {
        return $this->client->update($this->path(), $id, $data, $headers);
    }
}
