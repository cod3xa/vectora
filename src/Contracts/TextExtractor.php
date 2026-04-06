<?php

declare(strict_types=1);

namespace Vectora\Pinecone\Contracts;

/**
 * Extracts plain text from a file path (Phase 9 ingestion).
 */
interface TextExtractor
{
    /** Whether this extractor can handle the given filesystem path. */
    public function supports(string $path): bool;

    /**
     * @throws \RuntimeException If the file cannot be read or parsed
     */
    public function extract(string $path): string;
}
