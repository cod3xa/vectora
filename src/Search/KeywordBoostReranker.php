<?php

declare(strict_types=1);

namespace Vectora\Pinecone\Search;

use Vectora\Pinecone\Contracts\RerankerContract;
use Vectora\Pinecone\DTO\QueryVectorMatch;

/**
 * Hybrid-style reranking: boosts scores when metadata text contains query tokens.
 */
final class KeywordBoostReranker implements RerankerContract
{
    /**
     * @param  list<string>  $extraTokens  Additional required keywords (lowercased)
     */
    public function __construct(
        private readonly string $metadataTextKey = 'text',
        private readonly float $boostPerToken = 0.05,
        private readonly array $extraTokens = [],
    ) {}

    public function rerank(array $matches, string $queryText): array
    {
        $tokens = $this->tokenize($queryText);
        $tokens = array_merge($tokens, $this->extraTokens);
        $tokens = array_values(array_unique(array_map(strtolower(...), $tokens)));
        if ($tokens === []) {
            return $matches;
        }
        $scored = [];
        foreach ($matches as $m) {
            $text = $this->metadataText($m);
            $boost = 0.0;
            foreach ($tokens as $t) {
                if ($t !== '' && str_contains($text, $t)) {
                    $boost += $this->boostPerToken;
                }
            }
            $scored[] = new QueryVectorMatch(
                $m->id,
                $m->score + $boost,
                $m->values,
                $m->metadata,
            );
        }
        usort($scored, static fn (QueryVectorMatch $a, QueryVectorMatch $b): int => $b->score <=> $a->score);

        return $scored;
    }

    private function metadataText(QueryVectorMatch $m): string
    {
        $meta = $m->metadata ?? [];
        $v = $meta[$this->metadataTextKey] ?? '';

        return strtolower(is_string($v) ? $v : (string) json_encode($v));
    }

    /**
     * @return list<string>
     */
    private function tokenize(string $queryText): array
    {
        $parts = preg_split('/\s+/u', trim($queryText)) ?: [];

        return array_values(array_filter($parts, static fn (string $s): bool => $s !== ''));
    }
}
