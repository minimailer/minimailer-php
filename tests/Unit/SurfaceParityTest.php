<?php

declare(strict_types=1);

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

/*
 * Locks each resource's public surface to exactly the operations its v1
 * collection supports (mmm/openapi.yaml). A method the API does not offer
 * must not exist; an operation the API offers must. Drift fails loudly here.
 */
it('exposes exactly the spec operations', function (string $class, array $expected): void {
    $methods = array_map(
        static fn (ReflectionMethod $method): string => $method->getName(),
        (new ReflectionClass($class))->getMethods(ReflectionMethod::IS_PUBLIC),
    );

    $methods = array_values(array_diff($methods, ['__construct']));

    sort($methods);
    sort($expected);

    expect($methods)->toBe($expected);
})->with([
    'emails' => [Emails::class, ['list', 'get', 'create', 'delete', 'stats']],
    'domains' => [Domains::class, ['list', 'get', 'create', 'update', 'delete', 'records']],
    'webhooks' => [Webhooks::class, ['list', 'get', 'create', 'update', 'delete', 'deliveries']],
    'deliveries' => [Deliveries::class, ['get', 'replay']],
    'inbound' => [Inbound::class, ['list', 'get']],
    'inboxes' => [Inboxes::class, ['list', 'get', 'create', 'update', 'delete', 'inboundEmails']],
    'audiences' => [Audiences::class, ['list', 'get', 'create', 'update', 'delete', 'contacts', 'addContact', 'importContacts']],
    'contacts' => [Contacts::class, ['get', 'update', 'delete']],
    'campaigns' => [Campaigns::class, ['list', 'get', 'create', 'update', 'delete', 'stats']],
    'suppressions' => [Suppressions::class, ['list', 'delete']],
    'tokens' => [Tokens::class, ['create', 'delete']],
    'recipients' => [Recipients::class, ['forget', 'data']],
    'compliance' => [Compliance::class, ['createConsent', 'withdrawConsent', 'controllerAudit']],
]);
