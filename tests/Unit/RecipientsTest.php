<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response as GuzzleResponse;

it('queues erasure for a raw recipient email address', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([new GuzzleResponse(202)], $history);

    $response = $mm->recipients()->forget('user@example.com');

    expect($history[0]['request']->getMethod())->toBe('DELETE')
        ->and((string) $history[0]['request']->getUri())->toBe('https://api.minimailer.app/recipients/'.rawurlencode('user@example.com').'/forget')
        ->and((string) $history[0]['request']->getUri())->toBe('https://api.minimailer.app/recipients/user%40example.com/forget')
        ->and($response->status())->toBe(202);
});

it('queues a data export for a plus-addressed recipient', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([new GuzzleResponse(202)], $history);

    $response = $mm->recipients()->data('user+tag@example.com');

    expect($history[0]['request']->getMethod())->toBe('GET')
        ->and((string) $history[0]['request']->getUri())->toBe('https://api.minimailer.app/recipients/'.rawurlencode('user+tag@example.com').'/data')
        ->and((string) $history[0]['request']->getUri())->toBe('https://api.minimailer.app/recipients/user%2Btag%40example.com/data')
        ->and($response->status())->toBe(202);
});
