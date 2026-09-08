<?php

declare(strict_types=1);

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Request as GuzzleRequest;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use MiniMailer\Client;
use MiniMailer\Exceptions\ApiException;

it('normalises a base URL without a trailing slash', function (): void {
    $history = [];
    $mock = new MockHandler([new GuzzleResponse(200, [], '{"data":[]}')]);
    $stack = HandlerStack::create($mock);
    $stack->push(Middleware::history($history));

    $client = new Client('k', 'https://api.minimailer.app', new GuzzleClient(['handler' => $stack]));
    $client->list('domains');

    expect((string) $history[0]['request']->getUri())->toBe('https://api.minimailer.app/domains');
});

it('stamps Bearer auth and the JSON:API media type on every verb', function (string $verb): void {
    $history = [];
    $mock = new MockHandler([new GuzzleResponse(200, [], '{}')]);
    $stack = HandlerStack::create($mock);
    $stack->push(Middleware::history($history));

    $client = new Client('secret-key', 'https://api.minimailer.app/', new GuzzleClient(['handler' => $stack]));

    match ($verb) {
        'list' => $client->list('domains'),
        'get' => $client->get('domains', 'dom_1'),
        'create' => $client->create('domains', ['data' => ['type' => 'domains']]),
        'update' => $client->update('domains', 'dom_1', ['data' => ['type' => 'domains']]),
        'delete' => $client->delete('domains', 'dom_1'),
    };

    $request = $history[0]['request'];

    expect($request->getHeaderLine('Authorization'))->toBe('Bearer secret-key')
        ->and($request->getHeaderLine('Accept'))->toBe('application/vnd.api+json')
        ->and($request->getHeaderLine('Content-Type'))->toBe('application/vnd.api+json');
})->with(['list', 'get', 'create', 'update', 'delete']);

it('builds the query string on list()', function (): void {
    $history = [];
    $mock = new MockHandler([new GuzzleResponse(200, [], '{"data":[]}')]);
    $stack = HandlerStack::create($mock);
    $stack->push(Middleware::history($history));

    $client = new Client('k', 'https://api.minimailer.app/', new GuzzleClient(['handler' => $stack]));
    $client->list('outbound-emails', ['filter' => ['status' => 'delivered'], 'sort' => '-created_at']);

    expect((string) $history[0]['request']->getUri())
        ->toBe('https://api.minimailer.app/outbound-emails?'.http_build_query([
            'filter' => ['status' => 'delivered'],
            'sort' => '-created_at',
        ]));
});

it('lets a custom header through and win over the defaults', function (): void {
    $history = [];
    $mock = new MockHandler([new GuzzleResponse(201, [], '{}')]);
    $stack = HandlerStack::create($mock);
    $stack->push(Middleware::history($history));

    $client = new Client('k', 'https://api.minimailer.app/', new GuzzleClient(['handler' => $stack]));
    $client->create('domains', ['data' => ['type' => 'domains']], [
        'X-Request-Id' => 'req_123',
        'Accept' => 'application/json',
    ]);

    $request = $history[0]['request'];

    expect($request->getHeaderLine('X-Request-Id'))->toBe('req_123')
        ->and($request->getHeaderLine('Accept'))->toBe('application/json');
});

it('sends no body when the payload is empty', function (): void {
    $history = [];
    $mock = new MockHandler([new GuzzleResponse(202, [], '{}')]);
    $stack = HandlerStack::create($mock);
    $stack->push(Middleware::history($history));

    $client = new Client('k', 'https://api.minimailer.app/', new GuzzleClient(['handler' => $stack]));
    $client->create('deliveries/del_1/replay');

    expect((string) $history[0]['request']->getBody())->toBe('');
});

it('wraps an unencodable request body in an ApiException', function (): void {
    $client = new Client('k', 'https://api.minimailer.app/', new GuzzleClient([
        'handler' => HandlerStack::create(new MockHandler([])),
    ]));

    try {
        $client->create('domains', ['data' => ['name' => "\xB1\x31"]]);
        $this->fail('Expected an ApiException.');
    } catch (ApiException $exception) {
        expect($exception->getMessage())->toStartWith('Failed to encode the request body:')
            ->and($exception->response)->toBeNull()
            ->and($exception->getPrevious())->toBeInstanceOf(JsonException::class);
    }
});

it('wraps a PSR-18 transport failure in an ApiException', function (): void {
    $mock = new MockHandler([
        new ConnectException('Connection refused', new GuzzleRequest('GET', 'https://api.minimailer.app/domains')),
    ]);

    $client = new Client('k', 'https://api.minimailer.app/', new GuzzleClient([
        'handler' => HandlerStack::create($mock),
    ]));

    try {
        $client->list('domains');
        $this->fail('Expected an ApiException.');
    } catch (ApiException $exception) {
        expect($exception->getMessage())->toStartWith('HTTP request to the MiniMailer API failed:')
            ->and($exception->response)->toBeNull()
            ->and($exception->getPrevious())->toBeInstanceOf(ConnectException::class);
    }
});

it('wires a default HTTP client when none is injected, without sending anything', function (): void {
    expect(new Client('k'))->toBeInstanceOf(Client::class);
});

it('identifies itself with a minimailer-php User-Agent on every verb', function (string $verb): void {
    $history = [];
    $mock = new MockHandler([new GuzzleResponse(200, [], '{}')]);
    $stack = HandlerStack::create($mock);
    $stack->push(Middleware::history($history));

    $client = new Client('k', 'https://api.minimailer.app/', new GuzzleClient(['handler' => $stack]));

    match ($verb) {
        'list' => $client->list('domains'),
        'get' => $client->get('domains', 'dom_1'),
        'create' => $client->create('domains', ['data' => ['type' => 'domains']]),
        'update' => $client->update('domains', 'dom_1', ['data' => ['type' => 'domains']]),
        'delete' => $client->delete('domains', 'dom_1'),
    };

    expect($history[0]['request']->getHeaderLine('User-Agent'))
        ->toBe(Client::userAgent())
        ->toMatch('#^minimailer-php/\S+$#');
})->with(['list', 'get', 'create', 'update', 'delete']);

it('lets a caller-supplied User-Agent win', function (): void {
    $history = [];
    $mock = new MockHandler([new GuzzleResponse(200, [], '{}')]);
    $stack = HandlerStack::create($mock);
    $stack->push(Middleware::history($history));

    $client = new Client('k', 'https://api.minimailer.app/', new GuzzleClient(['handler' => $stack]));
    $client->create('domains', ['data' => ['type' => 'domains']], ['User-Agent' => 'my-app/1.0']);

    expect($history[0]['request']->getHeaderLine('User-Agent'))->toBe('my-app/1.0');
});

it('builds the default Guzzle client with connect and request timeouts', function (): void {
    // No request is sent here — only the constructed client's config is inspected.
    $client = new Client('k');

    $guzzle = (new ReflectionProperty(Client::class, 'httpClient'))->getValue($client);
    $config = (new ReflectionProperty(GuzzleClient::class, 'config'))->getValue($guzzle);

    expect($guzzle)->toBeInstanceOf(GuzzleClient::class)
        ->and($config['connect_timeout'])->toBe(Client::DEFAULT_HTTP_OPTIONS['connect_timeout'])
        ->and($config['timeout'])->toBe(Client::DEFAULT_HTTP_OPTIONS['timeout'])
        ->and(Client::DEFAULT_HTTP_OPTIONS['timeout'])->toBeGreaterThan(0.0)
        ->and(Client::DEFAULT_HTTP_OPTIONS['connect_timeout'])->toBeGreaterThan(0.0);
});

it('overrides default headers case-insensitively', function (string $name): void {
    $history = [];
    $mock = new MockHandler([new GuzzleResponse(200, [], '{}')]);
    $stack = HandlerStack::create($mock);
    $stack->push(Middleware::history($history));

    $client = new Client('k', 'https://api.minimailer.app/', new GuzzleClient(['handler' => $stack]));
    $client->create('domains', ['data' => ['type' => 'domains']], [$name => 'my-app/1.0']);

    $request = $history[0]['request'];

    expect($request->getHeader('User-Agent'))->toBe(['my-app/1.0'])
        ->and($request->getHeaderLine('Authorization'))->toBe('Bearer k');
})->with(['user-agent', 'USER-AGENT', 'User-Agent']);
