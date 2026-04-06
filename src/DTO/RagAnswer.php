<?php

declare(strict_types=1);

namespace Vectora\Pinecone\DTO;

/**
 * LLM answer with optional cited retrieval context.
 *
 * @param  list<RagSourceChunk>  $sources
 */
final readonly class RagAnswer
{
    /**
     * @param  list<RagSourceChunk>  $sources
     */
    public function __construct(
        public string $text,
        public array $sources = [],
    ) {}
}
