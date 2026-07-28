<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response as GuzzleResponse;

it('creates a webhook for domain events', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([
        new GuzzleResponse(201, ['Content-Type' => 'application/vnd.api+json'], '{"data":{"id":"wh_1","type":"webhooks","attributes":{"name":"events"}}}'),
    ], $history);

    $response = $mm->webhooks()->create([
        'data' => [
            'type' => 'webhooks',
            'attributes' => [
                'name' => 'events',
                'url' => 'https://example.com/hook',
                'events' => ['email.delivered'],
            ],
            'relationships' => [
                'domain' => [
                    'data' => ['type' => 'domains', 'id' => 'dom_1'],
                ],
            ],
        ],
    ]);

    expect($response->status())->toBe(201)
        ->and($response->json('data.id'))->toBe('wh_1');

    $request = $history[0]['request'];

    expect($request->getMethod())->toBe('POST')
        ->and((string) $request->getUri())->toBe('https://api.minimailer.app/webhooks')
        ->and($request->getHeaderLine('Authorization'))->toBe('Bearer test-key')
        ->and($request->getHeaderLine('Accept'))->toBe('application/vnd.api+json')
        ->and($request->getHeaderLine('Content-Type'))->toBe('application/vnd.api+json');

    $sent = json_decode((string) $request->getBody(), true);

    expect($sent['data']['type'])->toBe('webhooks')
        ->and($sent['data']['attributes']['name'])->toBe('events')
        ->and($sent['data']['attributes']['url'])->toBe('https://example.com/hook')
        ->and($sent['data']['attributes']['events'])->toBe(['email.delivered'])
        ->and($sent['data']['relationships']['domain']['data']['id'])->toBe('dom_1');
});

it('lists webhooks', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([new GuzzleResponse(200, [], '{"data":[{"id":"wh_1","type":"webhooks"}]}')], $history);

    $response = $mm->webhooks()->list();

    expect($history[0]['request']->getMethod())->toBe('GET')
        ->and((string) $history[0]['request']->getUri())->toBe('https://api.minimailer.app/webhooks')
        ->and($response->status())->toBe(200)
        ->and($response->json('data.0.id'))->toBe('wh_1');
});

it('shows a single webhook', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([new GuzzleResponse(200, [], '{"data":{"id":"wh_1","type":"webhooks"}}')], $history);

    $response = $mm->webhooks()->get('wh_1');

    expect($history[0]['request']->getMethod())->toBe('GET')
        ->and((string) $history[0]['request']->getUri())->toBe('https://api.minimailer.app/webhooks/wh_1')
        ->and($response->status())->toBe(200)
        ->and($response->json('data.id'))->toBe('wh_1');
});

it('deactivates a webhook', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([
        new GuzzleResponse(200, [], '{"data":{"id":"wh_1","type":"webhooks","attributes":{"active":false}}}'),
    ], $history);

    $response = $mm->webhooks()->update('wh_1', [
        'data' => [
            'type' => 'webhooks',
            'attributes' => ['active' => false],
        ],
    ]);

    expect($response->status())->toBe(200)
        ->and($response->json('data.attributes.active'))->toBeFalse();

    $request = $history[0]['request'];
    $sent = json_decode((string) $request->getBody(), true);

    expect($request->getMethod())->toBe('PATCH')
        ->and((string) $request->getUri())->toBe('https://api.minimailer.app/webhooks/wh_1')
        ->and($sent['data']['type'])->toBe('webhooks')
        ->and($sent['data']['attributes']['active'])->toBeFalse();
});

it('deletes a webhook', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([new GuzzleResponse(204)], $history);

    $response = $mm->webhooks()->delete('wh_1');

    expect($history[0]['request']->getMethod())->toBe('DELETE')
        ->and((string) $history[0]['request']->getUri())->toBe('https://api.minimailer.app/webhooks/wh_1')
        ->and($response->status())->toBe(204);
});

it('lists delivery attempts for a webhook', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([
        new GuzzleResponse(200, [], '{"data":[{"id":"del_1","type":"deliveries"}]}'),
    ], $history);

    $response = $mm->webhooks()->deliveries('wh_1');

    expect($history[0]['request']->getMethod())->toBe('GET')
        ->and((string) $history[0]['request']->getUri())->toBe('https://api.minimailer.app/webhooks/wh_1/relationships/deliveries')
        ->and($response->status())->toBe(200)
        ->and($response->json('data.0.id'))->toBe('del_1');
});

it('sorts delivery attempts for a webhook', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([
        new GuzzleResponse(200, [], '{"data":[{"id":"del_1","type":"deliveries"}]}'),
    ], $history);

    $response = $mm->webhooks()->deliveries('wh_1', ['sort' => '-created_at']);

    expect($history[0]['request']->getMethod())->toBe('GET')
        ->and((string) $history[0]['request']->getUri())
        ->toBe('https://api.minimailer.app/webhooks/wh_1/relationships/deliveries?'.http_build_query([
            'sort' => '-created_at',
        ]))
        ->and($response->status())->toBe(200)
        ->and($response->json('data.0.id'))->toBe('del_1');
});
