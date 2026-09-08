<?php

declare(strict_types=1);

namespace MiniMailer;

use Composer\InstalledVersions;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Psr7\Request;
use JsonException;
use MiniMailer\Contracts\ClientContract;
use MiniMailer\Exceptions\ApiException;
use MiniMailer\Exceptions\AuthenticationException;
use MiniMailer\Exceptions\AuthorizationException;
use MiniMailer\Exceptions\ConflictException;
use MiniMailer\Exceptions\RateLimitException;
use MiniMailer\Exceptions\UnsupportedMediaTypeException;
use MiniMailer\Exceptions\ValidationException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;

/**
 * Framework-agnostic HTTP layer for the MiniMailer JSON:API. Talks to any
 * PSR-18 client (Guzzle by default) and returns the SDK's own {@see Response}.
 */
final readonly class Client implements ClientContract
{
    private const CONTENT_TYPE = 'application/vnd.api+json';

    private const PACKAGE = 'minimailer/minimailer-php';

    /**
     * Options for the Guzzle client built when none is injected. Guzzle's own
     * defaults are `timeout: 0` (wait forever) — a hung upstream would block
     * the calling process indefinitely. Inject your own PSR-18 client to tune.
     *
     * @var array<string, float>
     */
    public const DEFAULT_HTTP_OPTIONS = [
        'connect_timeout' => 10.0,
        'timeout' => 30.0,
    ];

    private ClientInterface $httpClient;

    private string $baseUrl;

    public function __construct(
        private string $apiKey,
        string $baseUrl = 'https://api.minimailer.app/',
        ?ClientInterface $httpClient = null,
    ) {
        $this->baseUrl = rtrim($baseUrl, '/').'/';
        $this->httpClient = $httpClient ?? new GuzzleClient(self::DEFAULT_HTTP_OPTIONS);
    }

    /**
     * `minimailer-php/<installed version>` — identifies the SDK on every
     * request. Falls back to `dev` when the package is not Composer-installed
     * (e.g. a path checkout).
     */
    public static function userAgent(): string
    {
        $version = 'dev';

        if (class_exists(InstalledVersions::class)) {
            try {
                $version = InstalledVersions::getPrettyVersion(self::PACKAGE) ?? 'dev';
            } catch (\OutOfBoundsException) {
                // Not installed as a Composer package; keep the fallback.
            }
        }

        return 'minimailer-php/'.$version;
    }

    public function list(string $resource, array $query = []): Response
    {
        $path = $query === [] ? $resource : $resource.'?'.http_build_query($query, '', '&', PHP_QUERY_RFC3986);

        return $this->send('GET', $path);
    }

    public function get(string $resource, string $id): Response
    {
        return $this->send('GET', $resource.'/'.$id);
    }

    public function create(string $resource, array $data = [], array $headers = []): Response
    {
        return $this->send('POST', $resource, $data, $headers);
    }

    public function update(string $resource, string $id, array $data = [], array $headers = []): Response
    {
        return $this->send('PATCH', $resource.'/'.$id, $data, $headers);
    }

    public function delete(string $resource, string $id): Response
    {
        return $this->send('DELETE', $resource.'/'.$id);
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, string>  $headers
     */
    private function send(string $method, string $path, array $data = [], array $headers = []): Response
    {
        try {
            $body = $data === [] ? null : json_encode($data, JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new ApiException(message: 'Failed to encode the request body: '.$exception->getMessage(), code: 0, previous: $exception);
        }

        $request = new Request(
            $method,
            $this->baseUrl.ltrim($path, '/'),
            [
                'Authorization' => 'Bearer '.$this->apiKey,
                'Accept' => self::CONTENT_TYPE,
                'Content-Type' => self::CONTENT_TYPE,
                'User-Agent' => self::userAgent(),
            ],
            $body,
        );

        // withHeader() replaces case-insensitively, so a caller's `user-agent`
        // overrides the default instead of being appended next to it.
        foreach ($headers as $name => $value) {
            $request = $request->withHeader($name, $value);
        }

        try {
            $response = new Response($this->httpClient->sendRequest($request));
        } catch (ClientExceptionInterface $exception) {
            throw new ApiException(message: 'HTTP request to the MiniMailer API failed: '.$exception->getMessage(), code: 0, previous: $exception);
        }

        $this->throwForStatus($response);

        return $response;
    }

    private function throwForStatus(Response $response): void
    {
        $status = $response->status();

        if ($status < 400) {
            return;
        }

        $detail = $response->json('errors.0.detail');
        $message = is_string($detail) && $detail !== '' ? $detail : $response->body();

        throw match ($status) {
            401 => new AuthenticationException($response, $message),
            403 => new AuthorizationException($response, $message),
            409 => new ConflictException($response, $message),
            415 => new UnsupportedMediaTypeException($response, $message),
            422 => new ValidationException($response, $message),
            429 => new RateLimitException($response, $message),
            default => new ApiException(response: $response, message: $message),
        };
    }
}
