<?php

declare(strict_types=1);

namespace MiniMailer;

use MiniMailer\Contracts\ClientContract;
use MiniMailer\Contracts\MiniMailerContract;
use MiniMailer\Resources\Audiences;
use MiniMailer\Resources\Campaigns;
use MiniMailer\Resources\Contacts;
use MiniMailer\Resources\Domains;
use MiniMailer\Resources\Emails;
use MiniMailer\Resources\Inbound;
use MiniMailer\Resources\Inboxes;
use MiniMailer\Resources\Suppressions;
use MiniMailer\Resources\Tokens;
use MiniMailer\Resources\Webhooks;
use Override;

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

    #[Override]
    public function emails(): Emails
    {
        return new Emails($this->client);
    }

    #[Override]
    public function domains(): Domains
    {
        return new Domains($this->client);
    }

    #[Override]
    public function webhooks(): Webhooks
    {
        return new Webhooks($this->client);
    }

    #[Override]
    public function inbound(): Inbound
    {
        return new Inbound($this->client);
    }

    #[Override]
    public function inboxes(): Inboxes
    {
        return new Inboxes($this->client);
    }

    #[Override]
    public function audiences(): Audiences
    {
        return new Audiences($this->client);
    }

    #[Override]
    public function contacts(): Contacts
    {
        return new Contacts($this->client);
    }

    #[Override]
    public function campaigns(): Campaigns
    {
        return new Campaigns($this->client);
    }

    #[Override]
    public function suppressions(): Suppressions
    {
        return new Suppressions($this->client);
    }

    #[Override]
    public function tokens(): Tokens
    {
        return new Tokens($this->client);
    }
}
