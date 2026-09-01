<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response as GuzzleResponse;

it('creates an audience for a sending domain', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([
        new GuzzleResponse(201, ['Content-Type' => 'application/vnd.api+json'], '{"data":{"id":"aud_1","type":"audiences","attributes":{"name":"Newsletter"}}}'),
    ], $history);

    $response = $mm->audiences()->create([
        'data' => [
            'type' => 'audiences',
            'attributes' => ['name' => 'Newsletter'],
            'relationships' => [
                'domain' => [
                    'data' => ['type' => 'domains', 'id' => 'dom_1'],
                ],
            ],
        ],
    ]);

    expect($response->status())->toBe(201)
        ->and($response->json('data.id'))->toBe('aud_1');

    $request = $history[0]['request'];

    expect($request->getMethod())->toBe('POST')
        ->and((string) $request->getUri())->toBe('https://api.minimailer.app/audiences')
        ->and($request->getHeaderLine('Authorization'))->toBe('Bearer test-key')
        ->and($request->getHeaderLine('Accept'))->toBe('application/vnd.api+json')
        ->and($request->getHeaderLine('Content-Type'))->toBe('application/vnd.api+json');

    $sent = json_decode((string) $request->getBody(), true);

    expect($sent['data']['type'])->toBe('audiences')
        ->and($sent['data']['attributes']['name'])->toBe('Newsletter')
        ->and($sent['data']['relationships']['domain']['data']['type'])->toBe('domains')
        ->and($sent['data']['relationships']['domain']['data']['id'])->toBe('dom_1');
});

it('lists audiences', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([new GuzzleResponse(200, [], '{"data":[{"id":"aud_1","type":"audiences"}]}')], $history);

    $response = $mm->audiences()->list();

    expect($history[0]['request']->getMethod())->toBe('GET')
        ->and((string) $history[0]['request']->getUri())->toBe('https://api.minimailer.app/audiences')
        ->and($response->status())->toBe(200)
        ->and($response->json('data.0.id'))->toBe('aud_1');
});

it('shows a single audience', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([new GuzzleResponse(200, [], '{"data":{"id":"aud_1","type":"audiences"}}')], $history);

    $response = $mm->audiences()->get('aud_1');

    expect($history[0]['request']->getMethod())->toBe('GET')
        ->and((string) $history[0]['request']->getUri())->toBe('https://api.minimailer.app/audiences/aud_1')
        ->and($response->status())->toBe(200)
        ->and($response->json('data.id'))->toBe('aud_1');
});

it('renames an audience', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([
        new GuzzleResponse(200, [], '{"data":{"id":"aud_1","type":"audiences","attributes":{"name":"News"}}}'),
    ], $history);

    $response = $mm->audiences()->update('aud_1', [
        'data' => [
            'type' => 'audiences',
            'attributes' => ['name' => 'News'],
        ],
    ]);

    expect($response->status())->toBe(200)
        ->and($response->json('data.attributes.name'))->toBe('News');

    $request = $history[0]['request'];
    $sent = json_decode((string) $request->getBody(), true);

    expect($request->getMethod())->toBe('PATCH')
        ->and((string) $request->getUri())->toBe('https://api.minimailer.app/audiences/aud_1')
        ->and($sent['data']['type'])->toBe('audiences')
        ->and($sent['data']['attributes']['name'])->toBe('News');
});

it('deletes an audience', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([new GuzzleResponse(204)], $history);

    $response = $mm->audiences()->delete('aud_1');

    expect($history[0]['request']->getMethod())->toBe('DELETE')
        ->and((string) $history[0]['request']->getUri())->toBe('https://api.minimailer.app/audiences/aud_1')
        ->and($response->status())->toBe(204);
});

it('lists contacts in an audience', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([
        new GuzzleResponse(200, [], '{"data":[{"id":"con_1","type":"contacts"}]}'),
    ], $history);

    $response = $mm->audiences()->contacts('aud_1');

    expect($history[0]['request']->getMethod())->toBe('GET')
        ->and((string) $history[0]['request']->getUri())->toBe('https://api.minimailer.app/audiences/aud_1/contacts')
        ->and($response->status())->toBe(200)
        ->and($response->json('data.0.id'))->toBe('con_1');
});

it('adds a contact to an audience', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([
        new GuzzleResponse(201, [], '{"data":{"id":"con_1","type":"contacts","attributes":{"email":"a@example.com"}}}'),
    ], $history);

    $response = $mm->audiences()->addContact('aud_1', [
        'data' => [
            'type' => 'contacts',
            'attributes' => ['email' => 'a@example.com'],
        ],
    ]);

    expect($response->status())->toBe(201)
        ->and($response->json('data.attributes.email'))->toBe('a@example.com');

    $request = $history[0]['request'];
    $sent = json_decode((string) $request->getBody(), true);

    expect($request->getMethod())->toBe('POST')
        ->and((string) $request->getUri())->toBe('https://api.minimailer.app/audiences/aud_1/contacts')
        ->and($sent['data']['type'])->toBe('contacts')
        ->and($sent['data']['attributes']['email'])->toBe('a@example.com');
});

it('imports contacts and returns created and skipped counts', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([
        new GuzzleResponse(200, [], '{"data":{"type":"contact-imports","attributes":{"created":2,"skipped":0}}}'),
    ], $history);

    $response = $mm->audiences()->importContacts('aud_1', [
        'data' => [
            'type' => 'contacts',
            'attributes' => [
                'contacts' => [
                    ['email' => 'a@example.com'],
                    ['email' => 'b@example.com'],
                ],
            ],
        ],
    ]);

    expect($response->status())->toBe(200)
        ->and($response->json('data.type'))->toBe('contact-imports')
        ->and($response->json('data.attributes.created'))->toBe(2)
        ->and($response->json('data.attributes.skipped'))->toBe(0);

    $request = $history[0]['request'];
    $sent = json_decode((string) $request->getBody(), true);

    expect($request->getMethod())->toBe('POST')
        ->and((string) $request->getUri())->toBe('https://api.minimailer.app/audiences/aud_1/contacts/import')
        ->and($sent['data']['type'])->toBe('contacts')
        ->and($sent['data']['attributes']['contacts'][0]['email'])->toBe('a@example.com')
        ->and($sent['data']['attributes']['contacts'][1]['email'])->toBe('b@example.com');
});
