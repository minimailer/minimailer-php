<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response as GuzzleResponse;
use MiniMailer\Exceptions\ValidationException;

it('lists suppression entries', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([
        new GuzzleResponse(200, [], '{"data":[{"id":"supp_1","type":"suppressions"}]}'),
    ], $history);

    $response = $mm->suppressions()->list();

    expect($history[0]['request']->getMethod())->toBe('GET')
        ->and((string) $history[0]['request']->getUri())->toBe('https://api.minimailer.app/suppressions')
        ->and($response->status())->toBe(200)
        ->and($response->json('data.0.id'))->toBe('supp_1');
});

it('filters suppression entries by reason', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([
        new GuzzleResponse(200, [], '{"data":[{"id":"supp_1","type":"suppressions","attributes":{"reason":"unsubscribe"}}]}'),
    ], $history);

    $response = $mm->suppressions()->list(['filter' => ['reason' => 'unsubscribe']]);

    expect($history[0]['request']->getMethod())->toBe('GET')
        ->and((string) $history[0]['request']->getUri())
        ->toBe('https://api.minimailer.app/suppressions?'.http_build_query([
            'filter' => ['reason' => 'unsubscribe'],
        ]))
        ->and($response->status())->toBe(200)
        ->and($response->json('data.0.attributes.reason'))->toBe('unsubscribe');
});

it('deletes a reversible suppression entry', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([new GuzzleResponse(204)], $history);

    $response = $mm->suppressions()->delete('supp_1');

    expect($history[0]['request']->getMethod())->toBe('DELETE')
        ->and((string) $history[0]['request']->getUri())->toBe('https://api.minimailer.app/suppressions/supp_1')
        ->and($response->status())->toBe(204);
});

it('rejects deletion of a permanent suppression entry', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([
        new GuzzleResponse(422, [], '{"errors":[{"detail":"Permanent suppressions cannot be deleted."}]}'),
    ], $history);

    expect(fn () => $mm->suppressions()->delete('supp_1'))->toThrow(
        ValidationException::class,
        'Permanent suppressions cannot be deleted.',
    );

    expect($history[0]['request']->getMethod())->toBe('DELETE')
        ->and((string) $history[0]['request']->getUri())->toBe('https://api.minimailer.app/suppressions/supp_1');
});
