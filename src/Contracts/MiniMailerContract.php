<?php

declare(strict_types=1);

namespace MiniMailer\Contracts;

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

interface MiniMailerContract
{
    public function emails(): Emails;

    public function domains(): Domains;

    public function webhooks(): Webhooks;

    public function inbound(): Inbound;

    public function inboxes(): Inboxes;

    public function audiences(): Audiences;

    public function contacts(): Contacts;

    public function campaigns(): Campaigns;

    public function suppressions(): Suppressions;

    public function tokens(): Tokens;
}
