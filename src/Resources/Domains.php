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
 * Sending domains — the v1 `domains` collection. Verification is automatic:
 * MiniMailer polls DNS and advances `pending` → `verifying` → `active`; use
 * {@see records()} for the DNS records to publish.
 */
final class Domains extends Resource
{
    use CreatesResources;
    use DeletesResources;
    use ListsResources;
    use RetrievesResources;
    use UpdatesResources;

    protected function path(): string
    {
        return 'domains';
    }

    /**
     * The DNS records (DKIM/SPF/MAIL FROM) to publish for a domain.
     */
    public function records(string $id): Response
    {
        return $this->client->get($this->path(), rawurlencode($id).'/records');
    }
}
