<?php

declare(strict_types=1);

namespace MiniMailer\Contracts;

use MiniMailer\Response;

interface ClientContract
{
    /**
     * @param  array<string, mixed>  $query
     */
    public function list(string $resource, array $query = []): Response;

    public function get(string $resource, string $id): Response;

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, string>  $headers
     */
    public function create(string $resource, array $data = [], array $headers = []): Response;

    /**
     * @param  array<string, mixed>  $data
     * @param  array<string, string>  $headers
     */
    public function update(string $resource, string $id, array $data = [], array $headers = []): Response;

    public function delete(string $resource, string $id): Response;
}
