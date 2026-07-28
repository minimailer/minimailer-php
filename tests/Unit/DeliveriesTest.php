<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response as GuzzleResponse;

it('shows a single webhook delivery attempt', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([
        new GuzzleResponse(200, [], '{"data":{"id":"del_1","type":"deliveries"}}'),
    ], $history);

    $response = $mm->deliveries()->get('del_1');

    expect($history[0]['request']->getMethod())->toBe('GET')
        ->and((string) $history[0]['request']->getUri())->toBe('https://api.minimailer.app/deliveries/del_1')
        ->and($response->status())->toBe(200)
        ->and($response->json('data.id'))->toBe('del_1');
});

it('replays a webhook delivery with a bodyless post', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([new GuzzleResponse(202)], $history);

    $response = $mm->deliveries()->replay('del_1');
    $request = $history[0]['request'];

    expect($request->getMethod())->toBe('POST')
        ->and((string) $request->getUri())->toBe('https://api.minimailer.app/deliveries/del_1/replay')
        ->and((string) $request->getBody())->toBe('')
        ->and($response->status())->toBe(202);
});
