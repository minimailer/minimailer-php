<?php

declare(strict_types=1);

namespace MiniMailer\Resources;

use MiniMailer\Contracts\ClientContract;
use MiniMailer\Response;

/**
 * Base JSON:API resource. Concrete resources declare their collection path
 * and inherit the standard CRUD surface; they add resource-specific actions.
 */
abstract class Resource
{
    public function __construct(
        protected readonly ClientContract $client,
    ) {}

    abstract protected function path(): string;

    /**
     * @param  array<string, mixed>  $query
     */
    public function list(array $query = []): Response
    {
        return $this->client->list($this->path(), $query);
    }

    public function get(string $id): Response
    {
        return $this->client->get($this->path(), $id);
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, string>  $headers
     */
    public function create(array $data, array $headers = []): Response
    {
        return $this->client->create($this->path(), $data, $headers);
    }

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, string>  $headers
     */
    public function update(string $id, array $data, array $headers = []): Response
    {
        return $this->client->update($this->path(), $id, $data, $headers);
    }

    public function delete(string $id): Response
    {
        return $this->client->delete($this->path(), $id);
    }
}
