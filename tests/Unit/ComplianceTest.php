<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Response as GuzzleResponse;

it('creates a recipient consent record', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([
        new GuzzleResponse(201, ['Content-Type' => 'application/vnd.api+json'], '{"data":{"id":"consent_1","type":"recipient-consents","attributes":{"recipient_email":"a@example.com"}}}'),
    ], $history);

    $response = $mm->compliance()->createConsent([
        'data' => [
            'type' => 'recipient-consents',
            'attributes' => [
                'recipient_email' => 'a@example.com',
                'consent_type' => 'opt_in',
                'consent_source' => 'signup_form',
                'consent_text' => 'I agree',
            ],
            'relationships' => [
                'domain' => [
                    'data' => ['type' => 'domains', 'id' => 'dom_1'],
                ],
            ],
        ],
    ]);

    expect($response->status())->toBe(201)
        ->and($response->json('data.id'))->toBe('consent_1');

    $request = $history[0]['request'];

    expect($request->getMethod())->toBe('POST')
        ->and((string) $request->getUri())->toBe('https://api.minimailer.app/recipient-consents')
        ->and($request->getHeaderLine('Authorization'))->toBe('Bearer test-key')
        ->and($request->getHeaderLine('Accept'))->toBe('application/vnd.api+json')
        ->and($request->getHeaderLine('Content-Type'))->toBe('application/vnd.api+json');

    $sent = json_decode((string) $request->getBody(), true);

    expect($sent['data']['type'])->toBe('recipient-consents')
        ->and($sent['data']['attributes']['recipient_email'])->toBe('a@example.com')
        ->and($sent['data']['attributes']['consent_type'])->toBe('opt_in')
        ->and($sent['data']['attributes']['consent_source'])->toBe('signup_form')
        ->and($sent['data']['attributes']['consent_text'])->toBe('I agree')
        ->and($sent['data']['relationships']['domain']['data']['type'])->toBe('domains')
        ->and($sent['data']['relationships']['domain']['data']['id'])->toBe('dom_1');
});

it('queues a controller audit export', function (): void {
    $history = [];
    $mm = fakeMiniMailerWithHistory([new GuzzleResponse(202)], $history);

    $response = $mm->compliance()->controllerAudit();

    expect($history[0]['request']->getMethod())->toBe('GET')
        ->and((string) $history[0]['request']->getUri())->toBe('https://api.minimailer.app/controller-audit')
        ->and($response->status())->toBe(202);
});
