<?php

declare(strict_types=1);

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response as GuzzleResponse;
use MiniMailer\Client;
use MiniMailer\Exceptions\ApiException;
use MiniMailer\Exceptions\NotFoundException;

it('does not follow redirects by default', function (): void {
    expect(Client::DEFAULT_HTTP_OPTIONS['allow_redirects'])->toBeFalse();
});

it('surfaces a redirect response as an ApiException instead of a silent success', function (int $status): void {
    $mock = new MockHandler([new GuzzleResponse($status, ['Location' => '/moved'], '')]);
    $client = new Client('k', 'https://api.minimailer.app/', new GuzzleClient(['handler' => HandlerStack::create($mock)]));

    expect(fn () => $client->list('domains'))
        ->toThrow(ApiException::class, sprintf('Unexpected redirect (HTTP %d)', $status));
})->with([301, 302, 307, 308]);

it('still treats 2xx as success and 4xx as the mapped exception', function (): void {
    $mock = new MockHandler([
        new GuzzleResponse(204, [], ''),
        new GuzzleResponse(404, [], '{"errors":[{"status":"404","code":"not_found","detail":"Not Found."}]}'),
    ]);
    $client = new Client('k', 'https://api.minimailer.app/', new GuzzleClient(['handler' => HandlerStack::create($mock)]));

    expect($client->delete('domains', 'dom_1')->status())->toBe(204)
        ->and(fn () => $client->get('domains', 'dom_2'))->toThrow(NotFoundException::class, 'Not Found.');
});
