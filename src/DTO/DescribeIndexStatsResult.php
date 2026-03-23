<?php

declare(strict_types=1);

namespace Vectora\Pinecone\DTO;

/**
 * @param  array<string, NamespaceSummary>  $namespaces  keyed by namespace name (empty string = default)
 */
final readonly class DescribeIndexStatsResult
{
    /**
     * @param  array<string, NamespaceSummary>  $namespaces
     */
    public function __construct(
        public int $dimension,
        public int $totalVectorCount,
        public array $namespaces,
        public ?string $metric = null,
    ) {}
}
