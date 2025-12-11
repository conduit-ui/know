<?php

declare(strict_types=1);

namespace ConduitUI\Know;

use ConduitUI\Know\Adapters\ClaudeMemAdapter;
use ConduitUI\Know\Adapters\GitHubPkmAdapter;
use ConduitUI\Know\Contracts\KnowledgeStore;
use ConduitUI\Know\Stores\DatabaseStore;
use ConduitUI\Know\Stores\InMemoryStore;
use Illuminate\Support\ServiceProvider;

class KnowServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/know.php', 'know');

        $this->app->singleton(KnowledgeStore::class, function ($app) {
            $storeType = config('know.default_store', 'memory');

            return match ($storeType) {
                'database' => new DatabaseStore($app->make('db')->connection()),
                default => new InMemoryStore(),
            };
        });

        $this->app->singleton(Know::class, function ($app) {
            $know = new Know($app->make(KnowledgeStore::class));

            // Register sync adapters
            $this->registerAdapters($know);

            return $know;
        });
    }

    public function boot(): void
    {
        // Publish config
        $this->publishes([
            __DIR__ . '/../config/know.php' => config_path('know.php'),
        ], 'know-config');

        // Publish migrations
        $this->publishes([
            __DIR__ . '/../database/migrations' => database_path('migrations'),
        ], 'know-migrations');

        // Load migrations if running in console
        if ($this->app->runningInConsole()) {
            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
        }
    }

    protected function registerAdapters(Know $know): void
    {
        $syncManager = $know->sync();

        // Register Claude Mem adapter
        if (config('know.adapters.claude-mem.enabled', true)) {
            $adapter = new ClaudeMemAdapter(
                config('know.adapters.claude-mem.path', '~/.claude-mem/claude-mem.db')
            );
            $syncManager->register($adapter);
        }

        // Register GitHub PKM adapter
        if (config('know.adapters.github-pkm.enabled', false)) {
            $adapter = new GitHubPkmAdapter(
                config('know.adapters.github-pkm.local_path', '~/personal-knowledge-management'),
                config('know.adapters.github-pkm.repository', 'jordanpartridge/personal-knowledge-management')
            );
            $syncManager->register($adapter);
        }
    }
}
