<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response as GuzzleResponse;

it('sends a transactional email to POST outbound-emails on the v1 contract', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([
        new GuzzleResponse(201, ['Content-Type' => 'application/vnd.api+json'], (string) json_encode([
            'data' => ['id' => 'email_01JZX8K7Q2M3N4P5R6S7T8V9W2'],
        ])),
    ], $history);

    $response = $mm->emails()->create([
        'data' => [
            'type' => 'outbound-emails',
            'attributes' => [
                'from' => 'sender@example.com',
                'to' => 'recipient@example.com',
                'subject' => 'Hello',
                'html' => '<p>Hello</p>',
            ],
            'relationships' => [
                'domain' => ['type' => 'domains', 'id' => 'dom_123'],
            ],
        ],
    ]);

    expect($response->status())->toBe(201)
        ->and($response->json('data.id'))->toBe('email_01JZX8K7Q2M3N4P5R6S7T8V9W2');

    $request = $history[0]['request'];

    expect($request->getMethod())->toBe('POST')
        ->and((string) $request->getUri())->toBe('https://api.minimailer.app/outbound-emails')
        ->and($request->getHeaderLine('Authorization'))->toBe('Bearer test-key')
        ->and($request->getHeaderLine('Accept'))->toBe('application/vnd.api+json')
        ->and($request->getHeaderLine('Content-Type'))->toBe('application/vnd.api+json');

    $sent = json_decode((string) $request->getBody(), true);

    expect($sent['data']['type'])->toBe('outbound-emails')
        ->and($sent['data']['attributes']['subject'])->toBe('Hello');
});

it('resolves the friendly emails() accessor to the outbound-emails collection path', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([new GuzzleResponse(200, [], '{"data":[]}')], $history);

    $mm->emails()->list();

    expect((string) $history[0]['request']->getUri())->toBe('https://api.minimailer.app/outbound-emails');
});

it('lists outbound emails with filter and sort parameters', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([new GuzzleResponse(200, [], '{"data":[]}')], $history);

    $mm->emails()->list(['filter' => ['status' => 'delivered'], 'sort' => '-created_at']);

    $request = $history[0]['request'];

    expect($request->getMethod())->toBe('GET')
        ->and((string) $request->getUri())
        ->toBe('https://api.minimailer.app/outbound-emails?'.http_build_query([
            'filter' => ['status' => 'delivered'],
            'sort' => '-created_at',
        ]));
});

it('shows a single outbound email', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([
        new GuzzleResponse(200, [], '{"data":{"id":"email_1","type":"outbound-emails"}}'),
    ], $history);

    $response = $mm->emails()->get('email_1');

    expect($history[0]['request']->getMethod())->toBe('GET')
        ->and((string) $history[0]['request']->getUri())->toBe('https://api.minimailer.app/outbound-emails/email_1')
        ->and($response->json('data.id'))->toBe('email_1');
});

it('deletes a stored outbound email record', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([new GuzzleResponse(204)], $history);

    $response = $mm->emails()->delete('email_1');

    expect($history[0]['request']->getMethod())->toBe('DELETE')
        ->and((string) $history[0]['request']->getUri())->toBe('https://api.minimailer.app/outbound-emails/email_1')
        ->and($response->status())->toBe(204);
});

it('fetches per-send stats from the stats sub-resource', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([
        new GuzzleResponse(200, [], (string) json_encode([
            'data' => [
                'type' => 'outbound-email-stats',
                'attributes' => ['sends' => 1, 'delivered' => 1, 'bounced' => 0, 'complained' => 0, 'opened' => 1, 'clicked' => 0, 'rejected' => 0],
            ],
        ])),
    ], $history);

    $response = $mm->emails()->stats('email_1');

    expect($history[0]['request']->getMethod())->toBe('GET')
        ->and((string) $history[0]['request']->getUri())->toBe('https://api.minimailer.app/outbound-emails/email_1/stats')
        ->and($response->json('data.attributes.delivered'))->toBe(1);
});
