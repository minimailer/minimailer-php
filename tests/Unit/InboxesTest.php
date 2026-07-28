<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response as GuzzleResponse;

it('creates an inbound mailbox', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([
        new GuzzleResponse(201, ['Content-Type' => 'application/vnd.api+json'], '{"data":{"id":"inbox_1","type":"inboxes","attributes":{"name":"Support"}}}'),
    ], $history);

    $response = $mm->inboxes()->create([
        'data' => [
            'type' => 'inboxes',
            'attributes' => [
                'name' => 'Support',
                'address' => 'support@example.com',
            ],
        ],
    ]);

    expect($response->status())->toBe(201)
        ->and($response->json('data.id'))->toBe('inbox_1');

    $request = $history[0]['request'];

    expect($request->getMethod())->toBe('POST')
        ->and((string) $request->getUri())->toBe('https://api.minimailer.app/inboxes')
        ->and($request->getHeaderLine('Authorization'))->toBe('Bearer test-key')
        ->and($request->getHeaderLine('Accept'))->toBe('application/vnd.api+json')
        ->and($request->getHeaderLine('Content-Type'))->toBe('application/vnd.api+json');

    $sent = json_decode((string) $request->getBody(), true);

    expect($sent['data']['type'])->toBe('inboxes')
        ->and($sent['data']['attributes']['name'])->toBe('Support')
        ->and($sent['data']['attributes']['address'])->toBe('support@example.com');
});

it('lists inbound mailboxes', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([new GuzzleResponse(200, [], '{"data":[{"id":"inbox_1","type":"inboxes"}]}')], $history);

    $response = $mm->inboxes()->list();

    expect($history[0]['request']->getMethod())->toBe('GET')
        ->and((string) $history[0]['request']->getUri())->toBe('https://api.minimailer.app/inboxes')
        ->and($response->status())->toBe(200)
        ->and($response->json('data.0.id'))->toBe('inbox_1');
});

it('shows a single inbound mailbox', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([new GuzzleResponse(200, [], '{"data":{"id":"inbox_1","type":"inboxes"}}')], $history);

    $response = $mm->inboxes()->get('inbox_1');

    expect($history[0]['request']->getMethod())->toBe('GET')
        ->and((string) $history[0]['request']->getUri())->toBe('https://api.minimailer.app/inboxes/inbox_1')
        ->and($response->status())->toBe(200)
        ->and($response->json('data.id'))->toBe('inbox_1');
});

it('renames an inbound mailbox', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([
        new GuzzleResponse(200, [], '{"data":{"id":"inbox_1","type":"inboxes","attributes":{"name":"Help"}}}'),
    ], $history);

    $response = $mm->inboxes()->update('inbox_1', [
        'data' => [
            'type' => 'inboxes',
            'attributes' => ['name' => 'Help'],
        ],
    ]);

    expect($response->status())->toBe(200)
        ->and($response->json('data.attributes.name'))->toBe('Help');

    $request = $history[0]['request'];
    $sent = json_decode((string) $request->getBody(), true);

    expect($request->getMethod())->toBe('PATCH')
        ->and((string) $request->getUri())->toBe('https://api.minimailer.app/inboxes/inbox_1')
        ->and($sent['data']['type'])->toBe('inboxes')
        ->and($sent['data']['attributes']['name'])->toBe('Help');
});

it('deletes an inbound mailbox', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([new GuzzleResponse(204)], $history);

    $response = $mm->inboxes()->delete('inbox_1');

    expect($history[0]['request']->getMethod())->toBe('DELETE')
        ->and((string) $history[0]['request']->getUri())->toBe('https://api.minimailer.app/inboxes/inbox_1')
        ->and($response->status())->toBe(204);
});

it('lists inbound emails received by a mailbox', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([
        new GuzzleResponse(200, [], '{"data":[{"id":"in_1","type":"inbound-emails"}]}'),
    ], $history);

    $response = $mm->inboxes()->inboundEmails('inbox_1');

    expect($history[0]['request']->getMethod())->toBe('GET')
        ->and((string) $history[0]['request']->getUri())->toBe('https://api.minimailer.app/inboxes/inbox_1/relationships/inbound-emails')
        ->and($response->status())->toBe(200)
        ->and($response->json('data.0.id'))->toBe('in_1');
});
