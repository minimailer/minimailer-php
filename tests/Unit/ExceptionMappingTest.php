<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response as GuzzleResponse;
use MiniMailer\Exceptions\ApiException;
use MiniMailer\Exceptions\AuthenticationException;
use MiniMailer\Exceptions\AuthorizationException;
use MiniMailer\Exceptions\ConflictException;
use MiniMailer\Exceptions\MiniMailerException;
use MiniMailer\Exceptions\RateLimitException;
use MiniMailer\Exceptions\UnsupportedMediaTypeException;
use MiniMailer\Exceptions\ValidationException;
use MiniMailer\Response;

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

it('falls back to the base ApiException for unmapped error statuses', function (int $status): void {
    $mm = fakeMiniMailer([new GuzzleResponse($status, [], '{"errors":[{"detail":"nope"}]}')]);

    try {
        $mm->emails()->create(['data' => ['type' => 'outbound-emails']]);
        $this->fail('Expected an ApiException.');
    } catch (ApiException $exception) {
        expect($exception::class)->toBe(ApiException::class);
    }
})->with(['bad request' => 400, 'not found' => 404, 'server error' => 500]);

it('carries the response and uses the HTTP status as the exception code', function (): void {
    $mm = fakeMiniMailer([new GuzzleResponse(404, [], '{"errors":[{"detail":"No such domain."}]}')]);

    try {
        $mm->domains()->get('dom_missing');
        $this->fail('Expected an ApiException.');
    } catch (ApiException $exception) {
        expect($exception->getCode())->toBe(404)
            ->and($exception->response)->toBeInstanceOf(Response::class)
            ->and($exception->response->status())->toBe(404)
            ->and($exception->getMessage())->toBe('No such domain.');
    }
});

it('falls back to the raw body when the JSON:API detail is absent', function (): void {
    $mm = fakeMiniMailer([new GuzzleResponse(500, [], 'upstream exploded')]);

    expect(fn (): mixed => $mm->domains()->list())
        ->toThrow(ApiException::class, 'upstream exploded');
});

it('is catchable as the package base exception', function (): void {
    $mm = fakeMiniMailer([new GuzzleResponse(422, [], '{"errors":[{"detail":"nope"}]}')]);

    expect(fn (): mixed => $mm->emails()->create(['data' => ['type' => 'outbound-emails']]))
        ->toThrow(MiniMailerException::class);
});

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

it('returns no error objects when the error body is not JSON:API', function (): void {
    $mm = fakeMiniMailer([new GuzzleResponse(422, [], '"errors happened"')]);

    try {
        $mm->emails()->create(['data' => ['type' => 'outbound-emails']]);
        $this->fail('Expected a ValidationException.');
    } catch (ValidationException $exception) {
        expect($exception->errors())->toBe([]);
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

it('returns no retry delay when the Retry-After header is missing or not numeric', function (array $headers): void {
    $mm = fakeMiniMailer([new GuzzleResponse(429, $headers, '{}')]);

    try {
        $mm->emails()->create(['data' => ['type' => 'outbound-emails']]);
        $this->fail('Expected a RateLimitException.');
    } catch (RateLimitException $exception) {
        expect($exception->retryAfter())->toBeNull();
    }
})->with([
    'missing' => [[]],
    'http-date' => [['Retry-After' => 'Wed, 21 Oct 2026 07:28:00 GMT']],
]);
