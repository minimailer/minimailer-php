<?php

declare(strict_types=1);

namespace MiniMailer\Resources;

use Override;

/**
 * Webhook endpoints — the v1 `webhooks` collection.
 */
final class Webhooks extends Resource
{
    #[Override]
    protected function path(): string
    {
        return 'webhooks';
    }
}
