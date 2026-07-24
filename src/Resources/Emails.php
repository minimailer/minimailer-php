<?php

declare(strict_types=1);

namespace MiniMailer\Resources;

use MiniMailer\Response;
use Override;

/**
 * Outbound (transactional) emails — the v1 `outbound-emails` collection.
 */
final class Emails extends Resource
{
    #[Override]
    protected function path(): string
    {
        return 'outbound-emails';
    }

    public function stats(string $id): Response
    {
        return $this->client->get($this->path(), $id.'/stats');
    }
}
