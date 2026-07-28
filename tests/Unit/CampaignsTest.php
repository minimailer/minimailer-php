<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response as GuzzleResponse;

it('creates a campaign for an audience', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([
        new GuzzleResponse(201, ['Content-Type' => 'application/vnd.api+json'], '{"data":{"id":"camp_1","type":"campaigns","attributes":{"name":"Launch"}}}'),
    ], $history);

    $response = $mm->campaigns()->create([
        'data' => [
            'type' => 'campaigns',
            'attributes' => [
                'name' => 'Launch',
                'subject' => 'Hi',
                'body_html' => '<p>Hi</p>',
            ],
            'relationships' => [
                'audience' => [
                    'data' => ['type' => 'audiences', 'id' => 'aud_1'],
                ],
            ],
        ],
    ]);

    expect($response->status())->toBe(201)
        ->and($response->json('data.id'))->toBe('camp_1');

    $request = $history[0]['request'];

    expect($request->getMethod())->toBe('POST')
        ->and((string) $request->getUri())->toBe('https://api.minimailer.app/campaigns')
        ->and($request->getHeaderLine('Authorization'))->toBe('Bearer test-key')
        ->and($request->getHeaderLine('Accept'))->toBe('application/vnd.api+json')
        ->and($request->getHeaderLine('Content-Type'))->toBe('application/vnd.api+json');

    $sent = json_decode((string) $request->getBody(), true);

    expect($sent['data']['type'])->toBe('campaigns')
        ->and($sent['data']['attributes']['name'])->toBe('Launch')
        ->and($sent['data']['attributes']['subject'])->toBe('Hi')
        ->and($sent['data']['attributes']['body_html'])->toBe('<p>Hi</p>')
        ->and($sent['data']['relationships']['audience']['data']['type'])->toBe('audiences')
        ->and($sent['data']['relationships']['audience']['data']['id'])->toBe('aud_1');
});

it('lists campaigns', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([new GuzzleResponse(200, [], '{"data":[{"id":"camp_1","type":"campaigns"}]}')], $history);

    $response = $mm->campaigns()->list();

    expect($history[0]['request']->getMethod())->toBe('GET')
        ->and((string) $history[0]['request']->getUri())->toBe('https://api.minimailer.app/campaigns')
        ->and($response->status())->toBe(200)
        ->and($response->json('data.0.id'))->toBe('camp_1');
});

it('shows a single campaign', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([new GuzzleResponse(200, [], '{"data":{"id":"camp_1","type":"campaigns"}}')], $history);

    $response = $mm->campaigns()->get('camp_1');

    expect($history[0]['request']->getMethod())->toBe('GET')
        ->and((string) $history[0]['request']->getUri())->toBe('https://api.minimailer.app/campaigns/camp_1')
        ->and($response->status())->toBe(200)
        ->and($response->json('data.id'))->toBe('camp_1');
});

it('schedules a campaign through an update', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([
        new GuzzleResponse(200, [], '{"data":{"id":"camp_1","type":"campaigns","attributes":{"status":"scheduled","scheduled_at":"2026-08-01T09:00:00Z"}}}'),
    ], $history);

    $response = $mm->campaigns()->update('camp_1', [
        'data' => [
            'type' => 'campaigns',
            'attributes' => [
                'status' => 'scheduled',
                'scheduled_at' => '2026-08-01T09:00:00Z',
            ],
        ],
    ]);

    expect($response->status())->toBe(200)
        ->and($response->json('data.attributes.status'))->toBe('scheduled')
        ->and($response->json('data.attributes.scheduled_at'))->toBe('2026-08-01T09:00:00Z');

    $request = $history[0]['request'];
    $sent = json_decode((string) $request->getBody(), true);

    expect($request->getMethod())->toBe('PATCH')
        ->and((string) $request->getUri())->toBe('https://api.minimailer.app/campaigns/camp_1')
        ->and($sent['data']['type'])->toBe('campaigns')
        ->and($sent['data']['attributes']['status'])->toBe('scheduled')
        ->and($sent['data']['attributes']['scheduled_at'])->toBe('2026-08-01T09:00:00Z');
});

it('deletes a campaign', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([new GuzzleResponse(204)], $history);

    $response = $mm->campaigns()->delete('camp_1');

    expect($history[0]['request']->getMethod())->toBe('DELETE')
        ->and((string) $history[0]['request']->getUri())->toBe('https://api.minimailer.app/campaigns/camp_1')
        ->and($response->status())->toBe(204);
});

it('fetches campaign delivery statistics', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([
        new GuzzleResponse(200, [], '{"data":{"type":"campaign-stats","attributes":{"delivered":42}}}'),
    ], $history);

    $response = $mm->campaigns()->stats('camp_1');

    expect($history[0]['request']->getMethod())->toBe('GET')
        ->and((string) $history[0]['request']->getUri())->toBe('https://api.minimailer.app/campaigns/camp_1/stats')
        ->and($response->status())->toBe(200)
        ->and($response->json('data.type'))->toBe('campaign-stats')
        ->and($response->json('data.attributes.delivered'))->toBe(42);
});
