<?php

declare(strict_types=1);

namespace ConduitUI\Know\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static \Illuminate\Support\Collection how(string $query)
 * @method static \Illuminate\Support\Collection why(string $query)
 * @method static \Illuminate\Support\Collection what(string $query)
 * @method static \ConduitUI\Know\Data\Insight remember(\ConduitUI\Know\Data\Insight $insight)
 * @method static \Illuminate\Support\Collection search(string $query, array $types = [])
 * @method static \ConduitUI\Know\SyncManager sync()
 *
 * @see \ConduitUI\Know\Know
 */
class Know extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return \ConduitUI\Know\Know::class;
    }
}
