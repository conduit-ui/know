<?php

declare(strict_types=1);

namespace ConduitUI\Know;

use ConduitUI\Know\Contracts\KnowledgeStore;
use ConduitUI\Know\Data\Insight;
use Illuminate\Support\Collection;

class Know
{
    public function __construct(
        protected KnowledgeStore $store
    ) {}

    /**
     * How does something work? Returns patterns and implementations.
     */
    public function how(string $query): Collection
    {
        return $this->store->search($query, ['pattern', 'implementation', 'how-it-works']);
    }

    /**
     * Why was a decision made? Returns decisions and rationale.
     */
    public function why(string $query): Collection
    {
        return $this->store->search($query, ['decision', 'rationale', 'why-it-exists']);
    }

    /**
     * What is something? Returns facts and definitions.
     */
    public function what(string $query): Collection
    {
        return $this->store->search($query, ['fact', 'definition', 'discovery']);
    }

    /**
     * Remember an insight for future retrieval.
     */
    public function remember(Insight $insight): Insight
    {
        return $this->store->persist($insight);
    }

    /**
     * Search across all knowledge types.
     */
    public function search(string $query, array $types = []): Collection
    {
        return $this->store->search($query, $types);
    }

    /**
     * Get sync manager for cross-source operations.
     */
    public function sync(): SyncManager
    {
        return new SyncManager($this->store);
    }
}
