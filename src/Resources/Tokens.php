<?php

declare(strict_types=1);

namespace MiniMailer\Resources;

use MiniMailer\Resources\Concerns\CreatesResources;
use MiniMailer\Resources\Concerns\DeletesResources;

/**
 * API tokens — the v1 `tokens` collection. The plaintext token is returned
 * once on create; there is no list or show endpoint by design. The chosen
 * `duration` (`1_month`, `6_months`, `12_months`) is enforced server-side.
 */
final class Tokens extends Resource
{
    use CreatesResources;
    use DeletesResources;

    protected function path(): string
    {
        return 'tokens';
    }
}
