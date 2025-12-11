<?php

declare(strict_types=1);

namespace ConduitUI\Know\Adapters;

use ConduitUI\Know\Contracts\SyncAdapter;
use ConduitUI\Know\Data\Insight;
use DateTime;
use Illuminate\Support\Collection;
use PDO;

class ClaudeMemAdapter implements SyncAdapter
{
    protected ?PDO $connection = null;

    public function __construct(
        protected string $dbPath = '~/.claude-mem/claude-mem.db'
    ) {
        // Expand tilde in path
        if (str_starts_with($this->dbPath, '~/')) {
            $this->dbPath = $_SERVER['HOME'] . substr($this->dbPath, 1);
        }
    }

    public function name(): string
    {
        return 'claude-mem';
    }

    public function available(): bool
    {
        return file_exists($this->dbPath);
    }

    public function pull(): Collection
    {
        if (!$this->available()) {
            return collect();
        }

        $this->connect();

        $stmt = $this->connection->query('
            SELECT
                id,
                sdk_session_id,
                project,
                text,
                type,
                title,
                subtitle,
                facts,
                narrative,
                concepts,
                files_read,
                files_modified,
                prompt_number,
                created_at,
                created_at_epoch
            FROM observations
            ORDER BY created_at_epoch DESC
        ');

        $observations = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return collect($observations)->map(function ($obs) {
            return $this->mapObservationToInsight($obs);
        });
    }

    public function push(Insight $insight): bool
    {
        // claude-mem is read-only from our perspective
        // Writing to it would require reverse-engineering the observation structure
        return false;
    }

    protected function connect(): void
    {
        if ($this->connection !== null) {
            return;
        }

        $this->connection = new PDO('sqlite:' . $this->dbPath);
        $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    protected function mapObservationToInsight(array $obs): Insight
    {
        // Map claude-mem types to Know types
        $typeMap = [
            'decision' => 'decision',
            'bugfix' => 'pattern',
            'feature' => 'pattern',
            'refactor' => 'pattern',
            'discovery' => 'discovery',
            'change' => 'fact',
        ];

        $type = $typeMap[$obs['type']] ?? 'fact';

        // Build content from available fields
        $contentParts = [];

        if (!empty($obs['text'])) {
            $contentParts[] = $obs['text'];
        }

        if (!empty($obs['subtitle'])) {
            $contentParts[] = "\n## Context\n" . $obs['subtitle'];
        }

        if (!empty($obs['narrative'])) {
            $contentParts[] = "\n## Narrative\n" . $obs['narrative'];
        }

        if (!empty($obs['facts'])) {
            $contentParts[] = "\n## Facts\n" . $obs['facts'];
        }

        if (!empty($obs['concepts'])) {
            $contentParts[] = "\n## Concepts\n" . $obs['concepts'];
        }

        $content = implode("\n", $contentParts);

        // Build tags from available metadata
        $tags = [];

        if (!empty($obs['project'])) {
            $tags[] = 'project:' . $obs['project'];
        }

        if (!empty($obs['type'])) {
            $tags[] = 'observation:' . $obs['type'];
        }

        if (!empty($obs['files_read'])) {
            $tags[] = 'files-read';
        }

        if (!empty($obs['files_modified'])) {
            $tags[] = 'files-modified';
        }

        $createdAt = !empty($obs['created_at'])
            ? new DateTime($obs['created_at'])
            : null;

        return new Insight(
            title: $obs['title'] ?? 'Untitled Observation',
            content: $content,
            type: $type,
            tags: $tags,
            source: 'claude-mem',
            sourceId: (string) $obs['id'],
            project: $obs['project'] ?? null,
            createdAt: $createdAt,
        );
    }
}
