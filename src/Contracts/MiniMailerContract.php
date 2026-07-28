<?php

declare(strict_types=1);

namespace MiniMailer\Contracts;

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

interface MiniMailerContract
{
    public function emails(): Emails;

    public function domains(): Domains;

    public function webhooks(): Webhooks;

    public function deliveries(): Deliveries;

    public function inbound(): Inbound;

    public function inboxes(): Inboxes;

    public function audiences(): Audiences;

    public function contacts(): Contacts;

    public function campaigns(): Campaigns;

    public function suppressions(): Suppressions;

    public function tokens(): Tokens;

    public function recipients(): Recipients;

    public function compliance(): Compliance;
}
