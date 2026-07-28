<?php

declare(strict_types=1);

namespace MiniMailer;

use MiniMailer\Contracts\ClientContract;
use MiniMailer\Contracts\MiniMailerContract;
use MiniMailer\Resources\Audiences;
use MiniMailer\Resources\Campaigns;
use MiniMailer\Resources\Compliance;
use MiniMailer\Resources\Contacts;
use MiniMailer\Resources\Deliveries;
use MiniMailer\Resources\Domains;
use MiniMailer\Resources\Emails;
use MiniMailer\Resources\Inbound;
use MiniMailer\Resources\Inboxes;
use MiniMailer\Resources\Recipients;
use MiniMailer\Resources\Suppressions;
use MiniMailer\Resources\Tokens;
use MiniMailer\Resources\Webhooks;

/**
 * Entry point to the MiniMailer API. Wire it with an API key via {@see make()},
 * or inject a {@see ClientContract} directly (e.g. from a framework container).
 */
final readonly class MiniMailer implements MiniMailerContract
{
    public function __construct(
        private ClientContract $client,
    ) {}

    public static function make(string $apiKey, string $baseUrl = 'https://api.minimailer.app/'): self
    {
        return new self(new Client($apiKey, $baseUrl));
    }

    public function emails(): Emails
    {
        return new Emails($this->client);
    }

    public function domains(): Domains
    {
        return new Domains($this->client);
    }

    public function webhooks(): Webhooks
    {
        return new Webhooks($this->client);
    }

    public function deliveries(): Deliveries
    {
        return new Deliveries($this->client);
    }

    public function inbound(): Inbound
    {
        return new Inbound($this->client);
    }

    public function inboxes(): Inboxes
    {
        return new Inboxes($this->client);
    }

    public function audiences(): Audiences
    {
        return new Audiences($this->client);
    }

    public function contacts(): Contacts
    {
        return new Contacts($this->client);
    }

    public function campaigns(): Campaigns
    {
        return new Campaigns($this->client);
    }

    public function suppressions(): Suppressions
    {
        return new Suppressions($this->client);
    }

    public function tokens(): Tokens
    {
        return new Tokens($this->client);
    }

    public function recipients(): Recipients
    {
        return new Recipients($this->client);
    }

    public function compliance(): Compliance
    {
        return new Compliance($this->client);
    }
}
