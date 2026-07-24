<?php

declare(strict_types=1);

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use MiniMailer\Client;
use MiniMailer\MiniMailer;

it('sends a transactional email to POST outbound-emails on the v1 contract', function (): void {
    $history = [];
    $mock = new MockHandler([
        new GuzzleResponse(201, ['Content-Type' => 'application/vnd.api+json'], (string) json_encode([
            'data' => ['id' => 'email_01JZX8K7Q2M3N4P5R6S7T8V9W2'],
        ])),
    ]);
    $stack = HandlerStack::create($mock);
    $stack->push(Middleware::history($history));

    $mm = new MiniMailer(new Client(
        'test-key',
        'https://api.minimailer.app/',
        new GuzzleClient(['handler' => $stack]),
    ));

    $response = $mm->emails()->create([
        'data' => [
            'type' => 'outbound-emails',
            'attributes' => [
                'from' => 'sender@example.com',
                'to' => 'recipient@example.com',
                'subject' => 'Hello',
                'html' => '<p>Hello</p>',
            ],
            'relationships' => [
                'domain' => ['type' => 'domains', 'id' => 'dom_123'],
            ],
        ],
    ]);

    expect($response->status())->toBe(201)
        ->and($response->json('data.id'))->toBe('email_01JZX8K7Q2M3N4P5R6S7T8V9W2');

    $request = $history[0]['request'];

    expect($request->getMethod())->toBe('POST')
        ->and((string) $request->getUri())->toBe('https://api.minimailer.app/outbound-emails')
        ->and($request->getHeaderLine('Authorization'))->toBe('Bearer test-key')
        ->and($request->getHeaderLine('Accept'))->toBe('application/vnd.api+json')
        ->and($request->getHeaderLine('Content-Type'))->toBe('application/vnd.api+json');

    $sent = json_decode((string) $request->getBody(), true);

    expect($sent['data']['type'])->toBe('outbound-emails')
        ->and($sent['data']['attributes']['subject'])->toBe('Hello');
});

it('resolves the friendly emails() accessor to the outbound-emails collection path', function (): void {
    $history = [];
    $mock = new MockHandler([new GuzzleResponse(200, [], '{"data":[]}')]);
    $stack = HandlerStack::create($mock);
    $stack->push(Middleware::history($history));

    $mm = new MiniMailer(new Client('k', 'https://api.minimailer.app/', new GuzzleClient(['handler' => $stack])));
    $mm->emails()->list();

    expect((string) $history[0]['request']->getUri())->toBe('https://api.minimailer.app/outbound-emails');
});
