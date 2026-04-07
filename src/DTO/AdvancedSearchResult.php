<?php

declare(strict_types=1);

namespace Vectora\Pinecone\DTO;

use Vectora\Pinecone\Laravel\Search\AdvancedSearchBuilder;

/**
 * Result of {@see AdvancedSearchBuilder} (Phase 10).
 *
 * @param  list<QueryVectorMatch>  $matches
 * @param  array<string, array<string, int>>  $facets
 */
final readonly class AdvancedSearchResult
{
    /**
     * @param  list<QueryVectorMatch>  $matches
     * @param  array<string, array<string, int>>  $facets
     */
    public function __construct(
        public array $matches,
        public array $facets = [],
        public int $totalAvailable = 0,
        public ?int $page = null,
        public ?int $perPage = null,
    ) {}
}
