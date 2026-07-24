<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response as GuzzleResponse;
use MiniMailer\Exceptions\AuthenticationException;
use MiniMailer\Exceptions\AuthorizationException;
use MiniMailer\Exceptions\ConflictException;
use MiniMailer\Exceptions\RateLimitException;
use MiniMailer\Exceptions\UnsupportedMediaTypeException;
use MiniMailer\Exceptions\ValidationException;

it('maps v1 error status codes to typed exceptions', function (int $status, string $exception): void {
    $mm = fakeMiniMailer([new GuzzleResponse($status, [], '{"errors":[{"detail":"nope"}]}')]);

    expect(fn (): mixed => $mm->emails()->create(['data' => ['type' => 'outbound-emails']]))
        ->toThrow($exception);
})->with([
    'unauthorized' => [401, AuthenticationException::class],
    'forbidden' => [403, AuthorizationException::class],
    'conflict' => [409, ConflictException::class],
    'unsupported media type' => [415, UnsupportedMediaTypeException::class],
    'validation' => [422, ValidationException::class],
    'rate limited' => [429, RateLimitException::class],
]);

it('exposes JSON:API error objects on a ValidationException', function (): void {
    $mm = fakeMiniMailer([
        new GuzzleResponse(422, [], (string) json_encode([
            'errors' => [[
                'status' => '422',
                'code' => 'validation_failed',
                'detail' => 'The from field is required.',
                'source' => ['pointer' => '/data/attributes/from'],
            ]],
        ])),
    ]);

    try {
        $mm->emails()->create(['data' => ['type' => 'outbound-emails']]);
        $this->fail('Expected a ValidationException.');
    } catch (ValidationException $exception) {
        expect($exception->errors()[0]['detail'])->toBe('The from field is required.')
            ->and($exception->errors()[0]['source']['pointer'])->toBe('/data/attributes/from')
            ->and($exception->getMessage())->toBe('The from field is required.');
    }
});

it('carries the Retry-After header on a RateLimitException', function (): void {
    $mm = fakeMiniMailer([new GuzzleResponse(429, ['Retry-After' => '30'], '{}')]);

    try {
        $mm->emails()->create(['data' => ['type' => 'outbound-emails']]);
        $this->fail('Expected a RateLimitException.');
    } catch (RateLimitException $exception) {
        expect($exception->retryAfter())->toBe(30);
    }
});
