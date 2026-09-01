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
 * Marketing campaigns — the v1 `campaigns` collection. The lifecycle state
 * machine (schedule, pause, cancel) is driven through `update()`. Subject and
 * body fields support merge tags (`{{name}}`, `{{email}}`, `{{unsubscribe_url}}`,
 * `{{unsubscribe_mailto}}` and custom-field keys); the subject rejects CR/LF
 * with 422. The `audience` relationship is nullable — campaigns and their
 * stats survive audience deletion with `data: null` linkage.
 */
final class Campaigns extends Resource
{
    use CreatesResources;
    use DeletesResources;
    use ListsResources;
    use RetrievesResources;
    use UpdatesResources;

    protected function path(): string
    {
        return 'campaigns';
    }

    public function stats(string $id): Response
    {
        return $this->client->get($this->path(), rawurlencode($id).'/stats');
    }
}
