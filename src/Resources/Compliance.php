<?php

declare(strict_types=1);

namespace MiniMailer\Resources;

use MiniMailer\Response;

/**
 * GDPR compliance records — consent proof (`recipient-consents`) and the
 * controller audit export (`controller-audit`).
 */
final class Compliance extends Resource
{
    protected function path(): string
    {
        return 'recipient-consents';
    }

    /**
     * Append a consent record (GDPR Art. 7(1) proof).
     *
     * @param  array<string, mixed>  $data
     */
    public function createConsent(array $data): Response
    {
        return $this->client->create($this->path(), $data);
    }

    /**
     * Account-level audit export (GDPR Art. 28(3)(h)); asynchronous, 202.
     */
    public function controllerAudit(): Response
    {
        return $this->client->list('controller-audit');
    }
}
