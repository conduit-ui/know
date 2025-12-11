<?php

declare(strict_types=1);

namespace ConduitUI\Know\Stores;

use ConduitUI\Know\Contracts\KnowledgeStore;
use ConduitUI\Know\Data\Insight;
use DateTime;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Collection;

class DatabaseStore implements KnowledgeStore
{
    protected string $table = 'insights';

    public function __construct(
        protected ConnectionInterface $connection
    ) {}

    public function search(string $query, array $types = []): Collection
    {
        $builder = $this->connection->table($this->table);

        // Full-text search on title and content
        $builder->where(function ($q) use ($query) {
            $q->where('title', 'like', "%{$query}%")
                ->orWhere('content', 'like', "%{$query}%");
        });

        // Type filter
        if (!empty($types)) {
            $builder->whereIn('type', $types);
        }

        $results = $builder->orderBy('created_at', 'desc')->get();

        return $results->map(fn ($row) => $this->rowToInsight($row));
    }

    public function persist(Insight $insight): Insight
    {
        $data = [
            'title' => $insight->title,
            'content' => $insight->content,
            'type' => $insight->type,
            'tags' => json_encode($insight->tags),
            'source' => $insight->source,
            'source_id' => $insight->sourceId,
            'project' => $insight->project,
            'created_at' => $insight->createdAt?->format('Y-m-d H:i:s') ?? now()->format('Y-m-d H:i:s'),
            'updated_at' => now()->format('Y-m-d H:i:s'),
        ];

        if ($insight->id) {
            // Update existing
            $this->connection->table($this->table)
                ->where('id', $insight->id)
                ->update($data);

            return $insight;
        }

        // Check for duplicate by source + source_id
        if ($insight->source && $insight->sourceId) {
            $existing = $this->connection->table($this->table)
                ->where('source', $insight->source)
                ->where('source_id', $insight->sourceId)
                ->first();

            if ($existing) {
                $this->connection->table($this->table)
                    ->where('id', $existing->id)
                    ->update($data);

                return $insight->withId($existing->id);
            }
        }

        // Insert new
        $id = $this->connection->table($this->table)->insertGetId($data);

        return $insight->withId($id);
    }

    public function all(array $types = []): Collection
    {
        $builder = $this->connection->table($this->table);

        if (!empty($types)) {
            $builder->whereIn('type', $types);
        }

        $results = $builder->orderBy('created_at', 'desc')->get();

        return $results->map(fn ($row) => $this->rowToInsight($row));
    }

    public function find(string|int $id): ?Insight
    {
        $row = $this->connection->table($this->table)
            ->where('id', $id)
            ->first();

        return $row ? $this->rowToInsight($row) : null;
    }

    protected function rowToInsight(object $row): Insight
    {
        return new Insight(
            title: $row->title,
            content: $row->content,
            type: $row->type,
            tags: json_decode($row->tags ?? '[]', true),
            source: $row->source,
            sourceId: $row->source_id,
            project: $row->project,
            createdAt: $row->created_at ? new DateTime($row->created_at) : null,
            id: (string) $row->id,
        );
    }
}
