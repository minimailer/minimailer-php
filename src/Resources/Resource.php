<?php

declare(strict_types=1);

namespace MiniMailer\Resources;

use MiniMailer\Contracts\ClientContract;

/**
 * Base JSON:API resource. Concrete resources declare their collection path and
 * compose exactly the verb traits ({@see Concerns}) their collection supports
 * on the v1 API — a verb the API does not offer does not exist on the SDK.
 */
abstract class Resource
{
    public function __construct(
        protected readonly ClientContract $client,
    ) {}

    abstract protected function path(): string;
}
