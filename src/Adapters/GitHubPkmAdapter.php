<?php

declare(strict_types=1);

namespace ConduitUI\Know\Adapters;

use ConduitUI\Know\Contracts\SyncAdapter;
use ConduitUI\Know\Data\Insight;
use DateTime;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class GitHubPkmAdapter implements SyncAdapter
{
    protected array $folderTypeMap = [
        'decisions' => 'decision',
        'patterns' => 'pattern',
        'practices' => 'practice',
    ];

    public function __construct(
        protected string $localPath,
        protected ?string $repository = 'jordanpartridge/personal-knowledge-management'
    ) {
        // Expand tilde in path
        if (str_starts_with($this->localPath, '~/')) {
            $this->localPath = $_SERVER['HOME'] . substr($this->localPath, 1);
        }
    }

    public function name(): string
    {
        return 'github-pkm';
    }

    public function available(): bool
    {
        return is_dir($this->localPath) && is_dir($this->localPath . '/.git');
    }

    public function pull(): Collection
    {
        if (!$this->available()) {
            return collect();
        }

        $insights = collect();

        foreach ($this->folderTypeMap as $folder => $type) {
            $folderPath = $this->localPath . '/' . $folder;

            if (!is_dir($folderPath)) {
                continue;
            }

            $files = glob($folderPath . '/*.md');

            foreach ($files as $filePath) {
                $insight = $this->parseMarkdownFile($filePath, $type);
                if ($insight) {
                    $insights->push($insight);
                }
            }
        }

        return $insights;
    }

    public function push(Insight $insight): bool
    {
        if (!$this->available()) {
            return false;
        }

        // Determine folder based on insight type
        $folder = array_search($insight->type, $this->folderTypeMap);
        if ($folder === false) {
            // Default to practices for unknown types
            $folder = 'practices';
        }

        $folderPath = $this->localPath . '/' . $folder;

        // Create folder if it doesn't exist
        if (!is_dir($folderPath)) {
            mkdir($folderPath, 0755, true);
        }

        // Generate filename from title
        $filename = Str::slug($insight->title) . '.md';
        $filePath = $folderPath . '/' . $filename;

        // Build markdown content with frontmatter
        $content = $this->buildMarkdownContent($insight);

        // Write file
        file_put_contents($filePath, $content);

        return true;
    }

    protected function parseMarkdownFile(string $filePath, string $type): ?Insight
    {
        $content = file_get_contents($filePath);

        if ($content === false) {
            return null;
        }

        // Parse frontmatter
        $frontmatter = [];
        $body = $content;

        if (preg_match('/^---\s*\n(.*?)\n---\s*\n(.*)$/s', $content, $matches)) {
            $frontmatterYaml = $matches[1];
            $body = $matches[2];

            // Simple YAML parsing for common fields
            foreach (explode("\n", $frontmatterYaml) as $line) {
                if (strpos($line, ':') !== false) {
                    [$key, $value] = explode(':', $line, 2);
                    $frontmatter[trim($key)] = trim($value);
                }
            }
        }

        // Extract title from frontmatter or first H1
        $title = $frontmatter['title'] ?? null;

        if (!$title && preg_match('/^#\s+(.+)$/m', $body, $matches)) {
            $title = $matches[1];
        }

        if (!$title) {
            $title = basename($filePath, '.md');
        }

        // Extract tags
        $tags = [];
        if (isset($frontmatter['tags'])) {
            $tagsStr = trim($frontmatter['tags'], '[]');
            $tags = array_map('trim', explode(',', $tagsStr));
        }

        // Get project from frontmatter
        $project = $frontmatter['project'] ?? null;

        // Get created date
        $createdAt = null;
        if (isset($frontmatter['date'])) {
            try {
                $createdAt = new DateTime($frontmatter['date']);
            } catch (\Exception $e) {
                // Fallback to file modification time
                $createdAt = new DateTime('@' . filemtime($filePath));
            }
        } else {
            $createdAt = new DateTime('@' . filemtime($filePath));
        }

        // Use relative path as source ID
        $sourceId = str_replace($this->localPath . '/', '', $filePath);

        return new Insight(
            title: $title,
            content: trim($body),
            type: $type,
            tags: $tags,
            source: 'github-pkm',
            sourceId: $sourceId,
            project: $project,
            createdAt: $createdAt,
        );
    }

    protected function buildMarkdownContent(Insight $insight): string
    {
        $lines = ['---'];

        // Add frontmatter
        $lines[] = 'title: ' . $insight->title;

        if ($insight->createdAt) {
            $lines[] = 'date: ' . $insight->createdAt->format('Y-m-d');
        }

        if (!empty($insight->tags)) {
            $lines[] = 'tags: [' . implode(', ', $insight->tags) . ']';
        }

        if ($insight->project) {
            $lines[] = 'project: ' . $insight->project;
        }

        $lines[] = '---';
        $lines[] = '';

        // Add title as H1 if not already in content
        if (!str_starts_with($insight->content, '# ')) {
            $lines[] = '# ' . $insight->title;
            $lines[] = '';
        }

        // Add body
        $lines[] = $insight->content;

        return implode("\n", $lines);
    }
}
