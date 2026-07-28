<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response as GuzzleResponse;

it('lists inbound emails', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([
        new GuzzleResponse(200, [], '{"data":[{"id":"in_1","type":"inbound-emails"}]}'),
    ], $history);

    $response = $mm->inbound()->list();

    expect($history[0]['request']->getMethod())->toBe('GET')
        ->and((string) $history[0]['request']->getUri())->toBe('https://api.minimailer.app/inbound-emails')
        ->and($response->status())->toBe(200)
        ->and($response->json('data.0.id'))->toBe('in_1');
});

it('filters inbound emails by mailbox', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([
        new GuzzleResponse(200, [], '{"data":[{"id":"in_1","type":"inbound-emails"}]}'),
    ], $history);

    $response = $mm->inbound()->list(['inbox' => 'inbox_1']);

    expect($history[0]['request']->getMethod())->toBe('GET')
        ->and((string) $history[0]['request']->getUri())
        ->toBe('https://api.minimailer.app/inbound-emails?'.http_build_query([
            'inbox' => 'inbox_1',
        ]))
        ->and($response->status())->toBe(200)
        ->and($response->json('data.0.id'))->toBe('in_1');
});

it('shows a single inbound email', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([
        new GuzzleResponse(200, [], '{"data":{"id":"in_1","type":"inbound-emails"}}'),
    ], $history);

    $response = $mm->inbound()->get('in_1');

    expect($history[0]['request']->getMethod())->toBe('GET')
        ->and((string) $history[0]['request']->getUri())->toBe('https://api.minimailer.app/inbound-emails/in_1')
        ->and($response->status())->toBe(200)
        ->and($response->json('data.id'))->toBe('in_1');
});
