<?php

declare(strict_types=1);

namespace Vectora\Pinecone\Ingestion\Extractors;

use Vectora\Pinecone\Contracts\TextExtractor;

/** Reads UTF-8 / ASCII text files as-is. */
final class PlainTextExtractor implements TextExtractor
{
    /** @param  list<string>  $extensions */
    public function __construct(
        private readonly array $extensions = ['txt', 'md', 'markdown', 'csv'],
    ) {}

    public function supports(string $path): bool
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return in_array($ext, $this->extensions, true);
    }

    public function extract(string $path): string
    {
        if (! is_readable($path)) {
            throw new \RuntimeException(sprintf('Cannot read file [%s].', $path));
        }
        $raw = file_get_contents($path);
        if ($raw === false) {
            throw new \RuntimeException(sprintf('Failed to read file [%s].', $path));
        }

        return $raw;
    }
}
