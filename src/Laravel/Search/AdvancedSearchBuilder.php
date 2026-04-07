<?php

declare(strict_types=1);

namespace Vectora\Pinecone\Laravel\Search;

use Vectora\Pinecone\Contracts\EmbeddingDriver;
use Vectora\Pinecone\Contracts\RerankerContract;
use Vectora\Pinecone\Contracts\VectorStoreContract;
use Vectora\Pinecone\DTO\AdvancedSearchResult;
use Vectora\Pinecone\DTO\QueryVectorsRequest;
use Vectora\Pinecone\Search\FacetAggregator;
use Vectora\Pinecone\Search\KeywordBoostReranker;
use Vectora\Pinecone\Search\OffsetPagination;
use Vectora\Pinecone\Search\ScoreNormalizer;

/**
 * Fluent advanced search: vector query + optional keyword hybrid boost, rerank, facets, pagination, score normalization.
 */
final class AdvancedSearchBuilder
{
    private string $queryText = '';

    private ?int $fetchTopK = null;

    private ?string $namespace = null;

    /** @var array<string, mixed>|null */
    private ?array $filter = null;

    private bool $keywordBoost = false;

    private string $keywordMetadataKey = 'text';

    /** @var list<string> */
    private array $keywordExtraTokens = [];

    private ?RerankerContract $reranker = null;

    private bool $applyMinMaxNormalization = false;

    private bool $applySoftmaxNormalization = false;

    /** @var list<string> */
    private array $facetKeys = [];

    private ?int $page = null;

    private ?int $perPage = null;

    private float $softmaxTemperature = 1.0;

    /**
     * @param  array<string, mixed>  $searchConfig  `pinecone.search` slice
     */
    public function __construct(
        private readonly VectorStoreContract $store,
        private readonly EmbeddingDriver $embeddings,
        private readonly array $searchConfig = [],
    ) {}

    public function queryText(string $text): self
    {
        $this->queryText = $text;

        return $this;
    }

    /** How many hits to retrieve from the vector index before rerank/slice (use a buffer for pagination). */
    public function fetchTopK(int $k): self
    {
        $this->fetchTopK = max(1, $k);

        return $this;
    }

    private function resolvedFetchTopK(): int
    {
        if ($this->fetchTopK !== null) {
            return $this->fetchTopK;
        }

        $v = $this->searchConfig['default_fetch_top_k'] ?? 50;

        return max(1, (int) $v);
    }

    private function resolvedKeywordBoost(): float
    {
        $v = $this->searchConfig['keyword_boost_per_token'] ?? 0.05;

        return (float) $v;
    }

    public function namespace(?string $namespace): self
    {
        $this->namespace = $namespace;

        return $this;
    }

    /**
     * @param  array<string, mixed>|null  $filter
     */
    public function filter(?array $filter): self
    {
        $this->filter = $filter;

        return $this;
    }

    /**
     * Enable keyword hybrid boost on metadata text (see {@see KeywordBoostReranker}).
     *
     * @param  list<string>  $extraTokens
     */
    public function withKeywordBoost(string $metadataKey = 'text', array $extraTokens = []): self
    {
        $this->keywordBoost = true;
        $this->keywordMetadataKey = $metadataKey;
        $this->keywordExtraTokens = $extraTokens;

        return $this;
    }

    public function rerankWith(RerankerContract $reranker): self
    {
        $this->reranker = $reranker;

        return $this;
    }

    public function normalizeMinMax(): self
    {
        $this->applyMinMaxNormalization = true;
        $this->applySoftmaxNormalization = false;

        return $this;
    }

    public function normalizeSoftmax(float $temperature = 1.0): self
    {
        $this->applySoftmaxNormalization = true;
        $this->applyMinMaxNormalization = false;
        $this->softmaxTemperature = max(1e-9, $temperature);

        return $this;
    }

    /**
     * @param  list<string>  $metadataKeys
     */
    public function withFacets(array $metadataKeys): self
    {
        $this->facetKeys = $metadataKeys;

        return $this;
    }

    public function paginate(int $page, int $perPage): self
    {
        $this->page = max(1, $page);
        $this->perPage = max(1, $perPage);

        return $this;
    }

    public function execute(): AdvancedSearchResult
    {
        if ($this->queryText === '') {
            throw new \InvalidArgumentException('Advanced search requires queryText().');
        }

        $vector = $this->embeddings->embed($this->queryText);
        $req = new QueryVectorsRequest(
            vector: $vector,
            topK: $this->resolvedFetchTopK(),
            namespace: $this->namespace,
            filter: $this->filter,
        );
        $raw = $this->store->query($req);
        $matches = $raw->matches;

        if ($this->keywordBoost) {
            $reranker = new KeywordBoostReranker($this->keywordMetadataKey, $this->resolvedKeywordBoost(), $this->keywordExtraTokens);
            $matches = $reranker->rerank($matches, $this->queryText);
        }
        if ($this->reranker !== null) {
            $matches = $this->reranker->rerank($matches, $this->queryText);
        }
        if ($this->applyMinMaxNormalization) {
            $matches = ScoreNormalizer::minMax($matches);
        } elseif ($this->applySoftmaxNormalization) {
            $matches = ScoreNormalizer::softmax($matches, $this->softmaxTemperature);
        }

        $total = count($matches);
        $facets = $this->facetKeys !== []
            ? FacetAggregator::aggregate($matches, $this->facetKeys)
            : [];

        $page = $this->page;
        $perPage = $this->perPage;
        if ($page !== null && $perPage !== null) {
            [$matches, $total] = OffsetPagination::slice($matches, ($page - 1) * $perPage, $perPage);
        }

        return new AdvancedSearchResult(
            matches: $matches,
            facets: $facets,
            totalAvailable: $total,
            page: $page,
            perPage: $perPage,
        );
    }
}
