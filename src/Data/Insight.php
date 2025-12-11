<?php

declare(strict_types=1);

namespace ConduitUI\Know\Data;

use DateTimeInterface;

class Insight
{
    public function __construct(
        public readonly string $title,
        public readonly string $content,
        public readonly string $type,
        public readonly array $tags = [],
        public readonly ?string $source = null,
        public readonly ?string $sourceId = null,
        public readonly ?string $project = null,
        public readonly ?DateTimeInterface $createdAt = null,
        public readonly ?string $id = null,
    ) {}

    public static function decision(string $title, string $content, array $tags = []): self
    {
        return new self($title, $content, 'decision', $tags);
    }

    public static function pattern(string $title, string $content, array $tags = []): self
    {
        return new self($title, $content, 'pattern', $tags);
    }

    public static function fact(string $title, string $content, array $tags = []): self
    {
        return new self($title, $content, 'fact', $tags);
    }

    public static function discovery(string $title, string $content, array $tags = []): self
    {
        return new self($title, $content, 'discovery', $tags);
    }

    public function withId(string|int $id): self
    {
        return new self(
            $this->title,
            $this->content,
            $this->type,
            $this->tags,
            $this->source,
            $this->sourceId,
            $this->project,
            $this->createdAt,
            (string) $id,
        );
    }

    public function withSource(string $source, ?string $sourceId = null): self
    {
        return new self(
            $this->title,
            $this->content,
            $this->type,
            $this->tags,
            $source,
            $sourceId,
            $this->project,
            $this->createdAt,
            $this->id,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'type' => $this->type,
            'tags' => $this->tags,
            'source' => $this->source,
            'source_id' => $this->sourceId,
            'project' => $this->project,
            'created_at' => $this->createdAt?->format('Y-m-d H:i:s'),
        ];
    }
}
