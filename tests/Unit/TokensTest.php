<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response as GuzzleResponse;

it('creates a token and returns the plaintext value once', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([
        new GuzzleResponse(201, ['Content-Type' => 'application/vnd.api+json'], (string) json_encode([
            'data' => [
                'id' => 'tok_1',
                'type' => 'tokens',
                'attributes' => ['token' => 'plain-token-value'],
            ],
        ])),
    ], $history);

    $response = $mm->tokens()->create([
        'data' => [
            'type' => 'tokens',
            'attributes' => [
                'name' => 'ci',
                'scopes' => ['emails:send'],
                'duration' => '1_month',
            ],
        ],
    ]);

    expect($response->status())->toBe(201)
        ->and($response->json('data.attributes.token'))->toBe('plain-token-value');

    $request = $history[0]['request'];

    expect($request->getMethod())->toBe('POST')
        ->and((string) $request->getUri())->toBe('https://api.minimailer.app/tokens')
        ->and($request->getHeaderLine('Authorization'))->toBe('Bearer test-key')
        ->and($request->getHeaderLine('Accept'))->toBe('application/vnd.api+json')
        ->and($request->getHeaderLine('Content-Type'))->toBe('application/vnd.api+json');

    $sent = json_decode((string) $request->getBody(), true);

    expect($sent['data']['type'])->toBe('tokens')
        ->and($sent['data']['attributes']['name'])->toBe('ci')
        ->and($sent['data']['attributes']['scopes'])->toBe(['emails:send'])
        ->and($sent['data']['attributes']['duration'])->toBe('1_month');
});

it('deletes a token', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([new GuzzleResponse(204)], $history);

    $response = $mm->tokens()->delete('tok_1');

    expect($history[0]['request']->getMethod())->toBe('DELETE')
        ->and((string) $history[0]['request']->getUri())->toBe('https://api.minimailer.app/tokens/tok_1')
        ->and($response->status())->toBe(204);
});
