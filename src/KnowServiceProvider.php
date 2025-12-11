<?php

declare(strict_types=1);

namespace ConduitUI\Know;

use ConduitUI\Know\Contracts\KnowledgeStore;
use ConduitUI\Know\Stores\InMemoryStore;
use Illuminate\Support\ServiceProvider;

class KnowServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(KnowledgeStore::class, function () {
            // Default to in-memory, can be swapped for database/file store
            return new InMemoryStore();
        });

        $this->app->singleton(Know::class, function ($app) {
            return new Know($app->make(KnowledgeStore::class));
        });
    }

    public function boot(): void
    {
        // Future: publish config, migrations
    }
}
