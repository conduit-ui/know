<?php

declare(strict_types=1);

namespace ConduitUI\Know;

class SyncResult
{
    public function __construct(
        public readonly int $pulled,
        public readonly int $pushed,
        public readonly array $errors = [],
    ) {}

    public function successful(): bool
    {
        return empty($this->errors);
    }
}
