<?php

declare(strict_types=1);

namespace MiniMailer\Resources\Concerns;

use MiniMailer\Response;

/**
 * `DELETE /{collection}/{id}` — delete a resource.
 */
trait DeletesResources
{
    public function delete(string $id): Response
    {
        return $this->client->delete($this->path(), rawurlencode($id));
    }
}
