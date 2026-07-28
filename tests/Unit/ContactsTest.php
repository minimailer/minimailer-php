<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response as GuzzleResponse;

it('shows a single audience contact', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([
        new GuzzleResponse(200, [], '{"data":{"id":"con_1","type":"contacts"}}'),
    ], $history);

    $response = $mm->contacts()->get('con_1');

    expect($history[0]['request']->getMethod())->toBe('GET')
        ->and((string) $history[0]['request']->getUri())->toBe('https://api.minimailer.app/contacts/con_1')
        ->and($response->status())->toBe(200)
        ->and($response->json('data.id'))->toBe('con_1');
});

it('updates an audience contact name', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([
        new GuzzleResponse(200, [], '{"data":{"id":"con_1","type":"contacts","attributes":{"name":"Ada"}}}'),
    ], $history);

    $response = $mm->contacts()->update('con_1', [
        'data' => [
            'type' => 'contacts',
            'attributes' => ['name' => 'Ada'],
        ],
    ]);

    expect($response->status())->toBe(200)
        ->and($response->json('data.attributes.name'))->toBe('Ada');

    $request = $history[0]['request'];
    $sent = json_decode((string) $request->getBody(), true);

    expect($request->getMethod())->toBe('PATCH')
        ->and((string) $request->getUri())->toBe('https://api.minimailer.app/contacts/con_1')
        ->and($sent['data']['type'])->toBe('contacts')
        ->and($sent['data']['attributes']['name'])->toBe('Ada');
});

it('deletes an audience contact', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([new GuzzleResponse(204)], $history);

    $response = $mm->contacts()->delete('con_1');

    expect($history[0]['request']->getMethod())->toBe('DELETE')
        ->and((string) $history[0]['request']->getUri())->toBe('https://api.minimailer.app/contacts/con_1')
        ->and($response->status())->toBe(204);
});
