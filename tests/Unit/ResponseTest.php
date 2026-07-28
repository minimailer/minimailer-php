<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response as GuzzleResponse;
use MiniMailer\Response;

it('reports status and success', function (int $status, bool $successful): void {
    $response = new Response(new GuzzleResponse($status));

    expect($response->status())->toBe($status)
        ->and($response->successful())->toBe($successful);
})->with([
    'ok' => [200, true],
    'created' => [201, true],
    'accepted' => [202, true],
    'no content' => [204, true],
    'redirect is not success' => [301, false],
    'not found' => [404, false],
    'server error' => [500, false],
]);

it('reads the body once and memoises it', function (): void {
    $response = new Response(new GuzzleResponse(200, [], '{"data":{"id":"dom_1"}}'));

    expect($response->body())->toBe('{"data":{"id":"dom_1"}}')
        ->and($response->body())->toBe('{"data":{"id":"dom_1"}}');
});

it('rewinds a seekable stream that was already consumed', function (): void {
    $psr = new GuzzleResponse(200, [], '{"data":[]}');
    $psr->getBody()->getContents(); // exhaust the stream first

    expect((new Response($psr))->body())->toBe('{"data":[]}');
});

it('decodes JSON with and without a dot-notation key', function (): void {
    $response = new Response(new GuzzleResponse(200, [], (string) json_encode([
        'data' => ['id' => 'dom_1', 'attributes' => ['name' => 'example.com']],
        'errors' => [['detail' => 'first']],
    ])));

    expect($response->json())->toBe([
        'data' => ['id' => 'dom_1', 'attributes' => ['name' => 'example.com']],
        'errors' => [['detail' => 'first']],
    ])
        ->and($response->json('data.id'))->toBe('dom_1')
        ->and($response->json('data.attributes.name'))->toBe('example.com')
        ->and($response->json('errors.0.detail'))->toBe('first')
        ->and($response->json('data.missing'))->toBeNull()
        ->and($response->json('nope.nested'))->toBeNull();
});

it('returns an empty array for a non-JSON body', function (): void {
    $response = new Response(new GuzzleResponse(200, [], 'not json'));

    expect($response->json())->toBe([])
        ->and($response->json('data.id'))->toBeNull();
});

it('returns a header line or null when absent', function (): void {
    $response = new Response(new GuzzleResponse(200, ['Retry-After' => '30']));

    expect($response->header('Retry-After'))->toBe('30')
        ->and($response->header('X-Missing'))->toBeNull();
});

it('exposes the underlying PSR-7 response via toPsr()', function (): void {
    $psr = new GuzzleResponse(200);

    expect((new Response($psr))->toPsr())->toBe($psr);
});
