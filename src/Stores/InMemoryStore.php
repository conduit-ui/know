<?php

declare(strict_types=1);

namespace ConduitUI\Know\Stores;

use ConduitUI\Know\Contracts\KnowledgeStore;
use ConduitUI\Know\Data\Insight;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class InMemoryStore implements KnowledgeStore
{
    protected array $insights = [];

    public function search(string $query, array $types = []): Collection
    {
        $query = strtolower($query);

        return collect($this->insights)
            ->filter(function (Insight $insight) use ($query, $types) {
                // Type filter
                if (!empty($types) && !in_array($insight->type, $types)) {
                    return false;
                }

                // Text search
                return Str::contains(strtolower($insight->title), $query)
                    || Str::contains(strtolower($insight->content), $query)
                    || collect($insight->tags)->contains(fn ($tag) => Str::contains(strtolower($tag), $query));
            })
            ->values();
    }

    public function persist(Insight $insight): Insight
    {
        $id = $insight->id ?? (string) (count($this->insights) + 1);
        $insight = $insight->withId($id);
        $this->insights[$id] = $insight;

        return $insight;
    }

    public function all(array $types = []): Collection
    {
        $insights = collect($this->insights);

        if (!empty($types)) {
            $insights = $insights->filter(fn (Insight $i) => in_array($i->type, $types));
        }

        return $insights->values();
    }

    public function find(string|int $id): ?Insight
    {
        return $this->insights[(string) $id] ?? null;
    }
}
