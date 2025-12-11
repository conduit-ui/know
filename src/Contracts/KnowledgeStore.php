<?php

declare(strict_types=1);

namespace ConduitUI\Know\Contracts;

use ConduitUI\Know\Data\Insight;
use Illuminate\Support\Collection;

interface KnowledgeStore
{
    /**
     * Search for knowledge matching query and optional type filters.
     */
    public function search(string $query, array $types = []): Collection;

    /**
     * Persist an insight to the store.
     */
    public function persist(Insight $insight): Insight;

    /**
     * Get all insights, optionally filtered by type.
     */
    public function all(array $types = []): Collection;

    /**
     * Find insight by ID.
     */
    public function find(string|int $id): ?Insight;
}
