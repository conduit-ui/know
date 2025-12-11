# Know - Memory for AI Agents That Actually Makes Sense

[![Latest Version on Packagist](https://img.shields.io/packagist/v/conduit-ui/know.svg?style=flat-square)](https://packagist.org/packages/conduit-ui/know)
[![Total Downloads](https://img.shields.io/packagist/dt/conduit-ui/know.svg?style=flat-square)](https://packagist.org/packages/conduit-ui/know)
[![License](https://img.shields.io/packagist/l/conduit-ui/know.svg?style=flat-square)](https://packagist.org/packages/conduit-ui/know)

Stop dumping everything into vector databases and hoping semantic search figures it out. Start organizing knowledge the way developers actually think: how does this work? why was this decision made? what is this thing?

**Perfect for:** AI agent memory systems, developer knowledge bases, architectural decision records, pattern libraries

## Quick Start

```bash
composer require conduit-ui/know
```

```php
use ConduitUI\Know\Facades\Know;
use ConduitUI\Know\Data\Insight;

// Remember a decision
Know::remember(
    Insight::decision(
        'Use Saloon for HTTP',
        'Chose Saloon over Guzzle because we need request/response objects and middleware support',
        tags: ['architecture', 'http']
    )
);

// Ask questions naturally
$patterns = Know::how('authenticate with GitHub');
$decisions = Know::why('chose Saloon');
$definitions = Know::what('is a connector');
```

## Features

- **Natural query API** - `how()`, `why()`, `what()` methods that match how developers think
- **Typed insights** - Decisions, patterns, facts, discoveries with structured metadata
- **Multiple storage backends** - In-memory for testing, database for production, custom stores via interface
- **Sync across sources** - Pull from GitHub issues, push to Notion, sync between systems
- **Laravel integration** - Service provider, facade, zero config
- **Tag-based organization** - Find knowledge by project, domain, or custom taxonomy

## Why This Exists

Vector databases are great for semantic similarity. But when you're building an AI agent that needs to understand "why did we choose this architecture?" or "how does authentication work in this codebase?", you need structured knowledge with intent.

Know gives your agents a memory system organized by question type, not just embedding proximity.

## Usage

### Remembering Knowledge

```php
use ConduitUI\Know\Data\Insight;
use ConduitUI\Know\Facades\Know;

// Record a decision
Know::remember(
    Insight::decision(
        'Use Laravel Octane',
        'Moving to Octane for 3x performance improvement on API endpoints',
        tags: ['performance', 'infrastructure']
    )
);

// Record a pattern
Know::remember(
    Insight::pattern(
        'Repository Pattern',
        'All database access goes through repositories in app/Repositories/',
        tags: ['architecture', 'database']
    )
);

// Record a fact
Know::remember(
    Insight::fact(
        'Primary Database',
        'Production uses RDS PostgreSQL 14 in us-east-1',
        tags: ['infrastructure', 'database']
    )
);

// Record a discovery
Know::remember(
    Insight::discovery(
        'Rate Limit Pattern',
        'GitHub API returns rate limit info in X-RateLimit-* headers',
        tags: ['github', 'api']
    )
);
```

### Querying Knowledge

```php
// How does something work?
$patterns = Know::how('handle GitHub webhooks');
// Returns: patterns, implementations, how-it-works insights

// Why was a decision made?
$decisions = Know::why('chose PostgreSQL');
// Returns: decisions, rationale, why-it-exists insights

// What is something?
$definitions = Know::what('is Saloon');
// Returns: facts, definitions, discovery insights

// Search everything
$results = Know::search('authentication', types: ['decision', 'pattern']);
```

### Working with Results

```php
$insights = Know::how('authenticate');

foreach ($insights as $insight) {
    echo $insight->title;      // "OAuth2 Flow"
    echo $insight->content;    // "We use Laravel Passport for..."
    echo $insight->type;       // "pattern"
    print_r($insight->tags);   // ['auth', 'security']
    echo $insight->createdAt;  // Carbon instance
}
```

### Syncing Across Sources

```php
use ConduitUI\Know\Facades\Know;

// Pull from GitHub issues, push to your knowledge base
Know::sync()
    ->register(new GitHubIssuesAdapter($token))
    ->from('github')
    ->run();

// Export to another system
Know::sync()
    ->register(new NotionAdapter($apiKey))
    ->to('notion')
    ->run();
```

### Custom Storage

Implement `KnowledgeStore` for your own backend:

```php
use ConduitUI\Know\Contracts\KnowledgeStore;
use ConduitUI\Know\Data\Insight;
use Illuminate\Support\Collection;

class RedisKnowledgeStore implements KnowledgeStore
{
    public function search(string $query, array $types = []): Collection
    {
        // Your search logic
    }

    public function persist(Insight $insight): Insight
    {
        // Your persistence logic
    }

    public function all(array $types = []): Collection
    {
        // Return all insights
    }

    public function find(string|int $id): ?Insight
    {
        // Find by ID
    }
}
```

Then bind it in your service provider:

```php
$this->app->bind(
    \ConduitUI\Know\Contracts\KnowledgeStore::class,
    RedisKnowledgeStore::class
);
```

## Configuration

Publish the config file (optional):

```bash
php artisan vendor:publish --tag=know-config
```

The default in-memory store works out of the box for testing. For production, implement a persistent store.

## Use Cases

### AI Agent Memory

Give your AI agent structured memory about your codebase:

```php
// Agent learns during development
Know::remember(Insight::decision(
    'Use Job Batching',
    'Switched from sequential jobs to batched jobs for import performance',
    tags: ['performance', 'jobs']
));

// Agent recalls later when asked
$context = Know::why('use job batching');
// Agent can now explain the decision with full context
```

### Architectural Decision Records

Stop maintaining separate ADR markdown files:

```php
Know::remember(Insight::decision(
    'ADR-001: Event Sourcing',
    'Adopting event sourcing for order processing to enable time-travel debugging and audit trails',
    tags: ['adr', 'architecture', 'orders']
));
```

### Pattern Library

Document your team's patterns in code:

```php
Know::remember(Insight::pattern(
    'Controller Pattern',
    'Controllers are thin - validation in FormRequests, logic in Actions',
    tags: ['laravel', 'patterns']
));
```

## Related Packages

The conduit-ui ecosystem:

- **[conduit-ui/connector](https://github.com/conduit-ui/connector)** - GitHub API transport layer (foundation for GitHub sync adapters)

More packages coming soon.

## Requirements

- PHP 8.2 or higher
- Laravel 10.x, 11.x, or 12.x (for Laravel integration)
- Or use standalone with Illuminate/Support

## Testing

```bash
composer test
```

## Support

**Enterprise support available** - Need custom adapters, training, or priority support? Email jordan@partridge.rocks

**Community** - Open an issue on [GitHub](https://github.com/conduit-ui/know/issues) or contribute a PR.

## License

MIT License. See [LICENSE](LICENSE) for details.
