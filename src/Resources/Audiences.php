<?php

declare(strict_types=1);

namespace MiniMailer\Resources;

use MiniMailer\Resources\Concerns\CreatesResources;
use MiniMailer\Resources\Concerns\DeletesResources;
use MiniMailer\Resources\Concerns\ListsResources;
use MiniMailer\Resources\Concerns\RetrievesResources;
use MiniMailer\Resources\Concerns\UpdatesResources;
use MiniMailer\Response;

/**
 * Marketing audiences — the v1 `audiences` collection. Contacts are created
 * through an audience's nested contact routes; they are read, updated and
 * deleted flat on {@see Contacts}. Deleting an audience returns 409 while a
 * campaign targeting it is scheduled, sending or paused; campaigns and their
 * stats are retained after the audience is gone.
 */
final class Audiences extends Resource
{
    use CreatesResources;
    use DeletesResources;
    use ListsResources;
    use RetrievesResources;
    use UpdatesResources;

    protected function path(): string
    {
        return 'audiences';
    }

    /**
     * @param  array<string, mixed>  $query
     */
    public function contacts(string $id, array $query = []): Response
    {
        return $this->client->list($this->path().'/'.rawurlencode($id).'/contacts', $query);
    }

    /**
     * Add one contact to the audience.
     *
     * @param  array<string, mixed>  $data
     */
    public function addContact(string $id, array $data): Response
    {
        return $this->client->create($this->path().'/'.rawurlencode($id).'/contacts', $data);
    }

    /**
     * Bulk add contacts (dedupe-and-skip); returns 200 with created/skipped counts.
     *
     * @param  array<string, mixed>  $data
     */
    public function importContacts(string $id, array $data): Response
    {
        return $this->client->create($this->path().'/'.rawurlencode($id).'/contacts/import', $data);
    }
}
