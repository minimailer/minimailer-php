<?php

declare(strict_types=1);

namespace MiniMailer\Resources;

use MiniMailer\Resources\Concerns\DeletesResources;
use MiniMailer\Resources\Concerns\RetrievesResources;
use MiniMailer\Resources\Concerns\UpdatesResources;

/**
 * Audience contacts — the v1 `contacts` collection. Created via
 * {@see Audiences::addContact()} / {@see Audiences::importContacts()};
 * listed per audience via {@see Audiences::contacts()}.
 */
final class Contacts extends Resource
{
    use DeletesResources;
    use RetrievesResources;
    use UpdatesResources;

    protected function path(): string
    {
        return 'contacts';
    }
}
