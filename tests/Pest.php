<?php

declare(strict_types=1);

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use MiniMailer\Client;
use MiniMailer\MiniMailer;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * Build a MiniMailer client whose HTTP layer replays the given canned
 * responses (Guzzle {@see MockHandler}), so no real request leaves the box.
 *
 * @param  array<int, ResponseInterface>  $responses
 */
function fakeMiniMailer(array $responses): MiniMailer
{
    $guzzle = new GuzzleClient([
        'handler' => HandlerStack::create(new MockHandler($responses)),
    ]);

    return new MiniMailer(new Client('test-key', 'https://api.minimailer.app/', $guzzle));
}

/**
 * Same as {@see fakeMiniMailer()}, but records every outgoing request into
 * `$history` (Guzzle {@see Middleware::history}) so tests can assert the
 * method, URI, headers and encoded body actually sent. Still fully mocked —
 * nothing leaves the box.
 *
 * @param  array<int, ResponseInterface>  $responses
 * @param  array<int, array{request: RequestInterface, response: ?ResponseInterface}>  $history
 */
function fakeMiniMailerWithHistory(array $responses, array &$history): MiniMailer
{
    $stack = HandlerStack::create(new MockHandler($responses));
    $stack->push(Middleware::history($history));

    $guzzle = new GuzzleClient(['handler' => $stack]);

    return new MiniMailer(new Client('test-key', 'https://api.minimailer.app/', $guzzle));
}
