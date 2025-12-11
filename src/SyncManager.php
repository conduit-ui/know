<?php

declare(strict_types=1);

namespace ConduitUI\Know;

use ConduitUI\Know\Contracts\KnowledgeStore;
use ConduitUI\Know\Contracts\SyncAdapter;
use Illuminate\Support\Collection;

class SyncManager
{
    protected array $adapters = [];
    protected ?string $fromSource = null;
    protected ?string $toSource = null;

    public function __construct(
        protected KnowledgeStore $store
    ) {}

    /**
     * Register a sync adapter.
     */
    public function register(SyncAdapter $adapter): self
    {
        $this->adapters[$adapter->name()] = $adapter;
        return $this;
    }

    /**
     * Set source to pull from.
     */
    public function from(string $source): self
    {
        $this->fromSource = $source;
        return $this;
    }

    /**
     * Set destination to push to.
     */
    public function to(string $source): self
    {
        $this->toSource = $source;
        return $this;
    }

    /**
     * Execute the sync operation.
     */
    public function run(): SyncResult
    {
        $pulled = 0;
        $pushed = 0;
        $errors = [];

        // Pull from source
        if ($this->fromSource && isset($this->adapters[$this->fromSource])) {
            $adapter = $this->adapters[$this->fromSource];

            if ($adapter->available()) {
                $insights = $adapter->pull();
                foreach ($insights as $insight) {
                    $this->store->persist($insight);
                    $pulled++;
                }
            } else {
                $errors[] = "Adapter '{$this->fromSource}' is not available";
            }
        }

        // Push to destination
        if ($this->toSource && isset($this->adapters[$this->toSource])) {
            $adapter = $this->adapters[$this->toSource];

            if ($adapter->available()) {
                $insights = $this->store->all();
                foreach ($insights as $insight) {
                    if ($adapter->push($insight)) {
                        $pushed++;
                    }
                }
            } else {
                $errors[] = "Adapter '{$this->toSource}' is not available";
            }
        }

        // Reset for next chain
        $this->fromSource = null;
        $this->toSource = null;

        return new SyncResult($pulled, $pushed, $errors);
    }

    /**
     * Get available adapters.
     */
    public function adapters(): array
    {
        return array_keys($this->adapters);
    }
}
