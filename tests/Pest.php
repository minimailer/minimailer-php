<?php

declare(strict_types=1);

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use MiniMailer\Client;
use MiniMailer\MiniMailer;
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
