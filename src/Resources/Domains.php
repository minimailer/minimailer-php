<?php

declare(strict_types=1);

namespace MiniMailer\Resources;

use MiniMailer\Response;
use Override;

/**
 * Sending domains — the v1 `domains` collection.
 */
final class Domains extends Resource
{
    #[Override]
    protected function path(): string
    {
        return 'domains';
    }

    public function verify(string $id): Response
    {
        return $this->client->create($this->path().'/'.$id.'/verify');
    }
}
