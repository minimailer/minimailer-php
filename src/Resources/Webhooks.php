<?php

declare(strict_types=1);

namespace MiniMailer\Resources;

/**
 * Webhook endpoints — the v1 `webhooks` collection.
 */
final class Webhooks extends Resource
{
    protected function path(): string
    {
        return 'webhooks';
    }
}
