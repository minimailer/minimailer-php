<?php

declare(strict_types=1);

use MiniMailer\MiniMailer;
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

it('wires a default client via make() without sending anything', function (): void {
    expect(MiniMailer::make('key'))->toBeInstanceOf(MiniMailer::class);
});

it('resolves every accessor to its resource as a fresh instance', function (string $accessor, string $class): void {
    $mm = fakeMiniMailer([]);

    expect($mm->{$accessor}())->toBeInstanceOf($class)
        ->and($mm->{$accessor}())->not->toBe($mm->{$accessor}());
})->with([
    'emails' => ['emails', Emails::class],
    'domains' => ['domains', Domains::class],
    'webhooks' => ['webhooks', Webhooks::class],
    'deliveries' => ['deliveries', Deliveries::class],
    'inbound' => ['inbound', Inbound::class],
    'inboxes' => ['inboxes', Inboxes::class],
    'audiences' => ['audiences', Audiences::class],
    'contacts' => ['contacts', Contacts::class],
    'campaigns' => ['campaigns', Campaigns::class],
    'suppressions' => ['suppressions', Suppressions::class],
    'tokens' => ['tokens', Tokens::class],
    'recipients' => ['recipients', Recipients::class],
    'compliance' => ['compliance', Compliance::class],
]);
