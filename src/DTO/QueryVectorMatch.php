<?php

declare(strict_types=1);

namespace Vectora\Pinecone\DTO;

/**
 * One scored hit from a vector query.
 *
 * @param  array<float>|null  $values
 * @param  array<string, mixed>|null  $metadata
 */
final readonly class QueryVectorMatch
{
    /**
     * @param  array<float>|null  $values
     * @param  array<string, mixed>|null  $metadata
     */
    public function __construct(
        public string $id,
        public float $score,
        public ?array $values = null,
        public ?array $metadata = null,
    ) {}
}
