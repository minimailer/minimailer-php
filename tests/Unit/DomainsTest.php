<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response as GuzzleResponse;

it('creates a transactional sending domain', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([
        new GuzzleResponse(201, ['Content-Type' => 'application/vnd.api+json'], '{"data":{"id":"dom_1","type":"domains","attributes":{"name":"example.com"}}}'),
    ], $history);

    $response = $mm->domains()->create([
        'data' => [
            'type' => 'domains',
            'attributes' => [
                'name' => 'example.com',
                'purpose' => 'transactional',
            ],
        ],
    ]);

    expect($response->status())->toBe(201)
        ->and($response->json('data.id'))->toBe('dom_1');

    $request = $history[0]['request'];

    expect($request->getMethod())->toBe('POST')
        ->and((string) $request->getUri())->toBe('https://api.minimailer.app/domains')
        ->and($request->getHeaderLine('Authorization'))->toBe('Bearer test-key')
        ->and($request->getHeaderLine('Accept'))->toBe('application/vnd.api+json')
        ->and($request->getHeaderLine('Content-Type'))->toBe('application/vnd.api+json');

    $sent = json_decode((string) $request->getBody(), true);

    expect($sent['data']['type'])->toBe('domains')
        ->and($sent['data']['attributes']['name'])->toBe('example.com')
        ->and($sent['data']['attributes']['purpose'])->toBe('transactional');
});

it('lists sending domains', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([new GuzzleResponse(200, [], '{"data":[{"id":"dom_1","type":"domains"}]}')], $history);

    $response = $mm->domains()->list();

    expect($history[0]['request']->getMethod())->toBe('GET')
        ->and((string) $history[0]['request']->getUri())->toBe('https://api.minimailer.app/domains')
        ->and($response->status())->toBe(200)
        ->and($response->json('data.0.id'))->toBe('dom_1');
});

it('filters sending domains by status', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([new GuzzleResponse(200, [], '{"data":[{"id":"dom_1","type":"domains","attributes":{"status":"active"}}]}')], $history);

    $response = $mm->domains()->list(['filter' => ['status' => 'active']]);

    expect($history[0]['request']->getMethod())->toBe('GET')
        ->and((string) $history[0]['request']->getUri())
        ->toBe('https://api.minimailer.app/domains?'.http_build_query([
            'filter' => ['status' => 'active'],
        ]))
        ->and($response->status())->toBe(200)
        ->and($response->json('data.0.attributes.status'))->toBe('active');
});

it('shows a single sending domain', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([new GuzzleResponse(200, [], '{"data":{"id":"dom_1","type":"domains"}}')], $history);

    $response = $mm->domains()->get('dom_1');

    expect($history[0]['request']->getMethod())->toBe('GET')
        ->and((string) $history[0]['request']->getUri())->toBe('https://api.minimailer.app/domains/dom_1')
        ->and($response->status())->toBe(200)
        ->and($response->json('data.id'))->toBe('dom_1');
});

it('requires TLS for a sending domain', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([
        new GuzzleResponse(200, [], '{"data":{"id":"dom_1","type":"domains","attributes":{"tls_policy":"require"}}}'),
    ], $history);

    $response = $mm->domains()->update('dom_1', [
        'data' => [
            'type' => 'domains',
            'attributes' => ['tls_policy' => 'require'],
        ],
    ]);

    expect($response->status())->toBe(200)
        ->and($response->json('data.attributes.tls_policy'))->toBe('require');

    $request = $history[0]['request'];
    $sent = json_decode((string) $request->getBody(), true);

    expect($request->getMethod())->toBe('PATCH')
        ->and((string) $request->getUri())->toBe('https://api.minimailer.app/domains/dom_1')
        ->and($sent['data']['type'])->toBe('domains')
        ->and($sent['data']['attributes']['tls_policy'])->toBe('require');
});

it('deletes a sending domain', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([new GuzzleResponse(204)], $history);

    $response = $mm->domains()->delete('dom_1');

    expect($history[0]['request']->getMethod())->toBe('DELETE')
        ->and((string) $history[0]['request']->getUri())->toBe('https://api.minimailer.app/domains/dom_1')
        ->and($response->status())->toBe(204);
});

it('fetches the DNS records for a sending domain', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([
        new GuzzleResponse(200, [], '{"data":[{"id":"rec_1","type":"domain-records"}]}'),
    ], $history);

    $response = $mm->domains()->records('dom_1');

    expect($history[0]['request']->getMethod())->toBe('GET')
        ->and((string) $history[0]['request']->getUri())->toBe('https://api.minimailer.app/domains/dom_1/relationships/records')
        ->and($response->status())->toBe(200)
        ->and($response->json('data.0.id'))->toBe('rec_1');
});
