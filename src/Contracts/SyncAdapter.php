<?php

declare(strict_types=1);

namespace ConduitUI\Know\Contracts;

use ConduitUI\Know\Data\Insight;
use Illuminate\Support\Collection;

interface SyncAdapter
{
    /**
     * Get the adapter identifier.
     */
    public function name(): string;

    /**
     * Pull insights from the external source.
     */
    public function pull(): Collection;

    /**
     * Push an insight to the external source.
     */
    public function push(Insight $insight): bool;

    /**
     * Check if the adapter is available/configured.
     */
    public function available(): bool;
}
