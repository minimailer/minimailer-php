<?php

declare(strict_types=1);

namespace MiniMailer;

use Psr\Http\Message\ResponseInterface;

/**
 * A thin, framework-agnostic wrapper around a PSR-7 response so the SDK never
 * leaks a concrete HTTP client's response type across its public surface.
 */
final class Response
{
    private ?string $raw = null;

    public function __construct(
        private readonly ResponseInterface $response,
    ) {}

    public function status(): int
    {
        return $this->response->getStatusCode();
    }

    public function successful(): bool
    {
        return $this->status() >= 200 && $this->status() < 300;
    }

    public function body(): string
    {
        if ($this->raw === null) {
            $stream = $this->response->getBody();

            if ($stream->isSeekable()) {
                $stream->rewind();
            }

            $this->raw = $stream->getContents();
        }

        return $this->raw;
    }

    /**
     * Decode the JSON body. With no key, returns the whole decoded array;
     * with a dot-notation key (e.g. `data.id`, `errors.0.detail`), returns
     * the value at that path or null when absent.
     */
    public function json(?string $key = null): mixed
    {
        $decoded = json_decode($this->body(), true);

        if (! is_array($decoded)) {
            $decoded = [];
        }

        if ($key === null) {
            return $decoded;
        }

        return $this->dataGet($decoded, $key);
    }

    public function header(string $name): ?string
    {
        $line = $this->response->getHeaderLine($name);

        return $line === '' ? null : $line;
    }

    public function toPsr(): ResponseInterface
    {
        return $this->response;
    }

    /**
     * @param  array<array-key, mixed>  $array
     */
    private function dataGet(array $array, string $key): mixed
    {
        $value = $array;

        foreach (explode('.', $key) as $segment) {
            if (is_array($value) && array_key_exists($segment, $value)) {
                $value = $value[$segment];

                continue;
            }

            return null;
        }

        return $value;
    }
}
