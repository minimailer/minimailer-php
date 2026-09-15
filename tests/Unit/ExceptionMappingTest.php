<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response as GuzzleResponse;
use MiniMailer\Exceptions\ApiException;
use MiniMailer\Exceptions\AuthenticationException;
use MiniMailer\Exceptions\AuthorizationException;
use MiniMailer\Exceptions\ConflictException;
use MiniMailer\Exceptions\MiniMailerException;
use MiniMailer\Exceptions\NotFoundException;
use MiniMailer\Exceptions\PaymentRequiredException;
use MiniMailer\Exceptions\RateLimitException;
use MiniMailer\Exceptions\UnsupportedMediaTypeException;
use MiniMailer\Exceptions\ValidationException;
use MiniMailer\Response;

/**
 * The exact error document the API returns for every 404, whether the record
 * does not exist or belongs to another account.
 */
const NOT_FOUND_BODY = '{"errors":[{"status":"404","code":"not_found","detail":"Not Found."}]}';

it('maps v1 error status codes to typed exceptions', function (int $status, string $exception): void {
    $mm = fakeMiniMailer([new GuzzleResponse($status, [], '{"errors":[{"detail":"nope"}]}')]);

    expect(fn (): mixed => $mm->emails()->create(['data' => ['type' => 'outbound-emails']]))
        ->toThrow($exception);
})->with([
    'unauthorized' => [401, AuthenticationException::class],
    'payment required' => [402, PaymentRequiredException::class],
    'forbidden' => [403, AuthorizationException::class],
    'not found' => [404, NotFoundException::class],
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
})->with(['bad request' => 400, 'server error' => 500]);

it('carries the response and uses the HTTP status as the exception code', function (): void {
    $mm = fakeMiniMailer([new GuzzleResponse(404, [], NOT_FOUND_BODY)]);

    try {
        $mm->domains()->get('dom_missing');
        $this->fail('Expected a NotFoundException.');
    } catch (NotFoundException $exception) {
        expect($exception->getCode())->toBe(404)
            ->and($exception->response)->toBeInstanceOf(Response::class)
            ->and($exception->response->status())->toBe(404)
            ->and($exception->getMessage())->toBe('Not Found.')
            ->and($exception->errorCode())->toBe('not_found');
    }
});

it('surfaces an unknown id and another account\'s id as the same not-found error', function (): void {
    $mm = fakeMiniMailer([
        new GuzzleResponse(404, [], NOT_FOUND_BODY),
        new GuzzleResponse(404, [], NOT_FOUND_BODY),
    ]);

    $seen = [];

    foreach (['dom_01J0000000000000000000UNKN', 'dom_01J00000000000000000FOREIGN'] as $id) {
        try {
            $mm->domains()->get($id);
            $this->fail('Expected a NotFoundException.');
        } catch (NotFoundException $exception) {
            $seen[] = [$exception::class, $exception->getCode(), $exception->errorCode(), $exception->getMessage()];
        }
    }

    expect($seen)->toHaveCount(2)
        ->and($seen[0])->toBe($seen[1])
        ->and($seen[0])->toBe([NotFoundException::class, 404, 'not_found', 'Not Found.']);
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

it('exposes the JSON:API error code so callers branch on it rather than the detail', function (int $status, string $exception, string $code): void {
    $mm = fakeMiniMailer([
        new GuzzleResponse($status, [], (string) json_encode([
            'errors' => [['status' => (string) $status, 'code' => $code, 'detail' => 'wording is not the contract']],
        ])),
    ]);

    try {
        $mm->emails()->create(['data' => ['type' => 'outbound-emails']]);
        $this->fail('Expected an ApiException.');
    } catch (ApiException $caught) {
        expect($caught)->toBeInstanceOf($exception)
            ->and($caught->errorCode())->toBe($code);
    }
})->with([
    'missing scope' => [403, AuthorizationException::class, 'missing_scope'],
    'agent category conflict' => [409, ConflictException::class, 'agent_category_conflict'],
    'agent throttled' => [429, RateLimitException::class, 'agent_throttled'],
    'monthly allowance exhausted' => [402, PaymentRequiredException::class, 'payment_required'],
]);

it('returns no error code when the body is not a JSON:API error document', function (string $body): void {
    $mm = fakeMiniMailer([new GuzzleResponse(500, [], $body)]);

    try {
        $mm->domains()->list();
        $this->fail('Expected an ApiException.');
    } catch (ApiException $exception) {
        expect($exception->errorCode())->toBeNull();
    }
})->with([
    'plain text' => 'upstream exploded',
    'code missing' => '{"errors":[{"detail":"nope"}]}',
    'code empty' => '{"errors":[{"code":"","detail":"nope"}]}',
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
