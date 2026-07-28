<?php

declare(strict_types=1);

namespace MiniMailer\Resources;

use MiniMailer\Response;

/**
 * GDPR recipient rights — the v1 `recipients` routes. The path parameter is
 * the recipient's raw email address, URL-encoded by these methods. Both
 * operations are asynchronous and return 202.
 */
final class Recipients extends Resource
{
    protected function path(): string
    {
        return 'recipients';
    }

    /**
     * Right to erasure (GDPR Art. 17): queue an asynchronous scrub.
     */
    public function forget(string $email): Response
    {
        return $this->client->delete($this->path(), rawurlencode($email).'/forget');
    }

    /**
     * Subject-access export (GDPR Art. 15): queue an asynchronous data export.
     */
    public function data(string $email): Response
    {
        return $this->client->get($this->path(), rawurlencode($email).'/data');
    }
}
