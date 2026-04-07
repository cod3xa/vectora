<?php

declare(strict_types=1);

namespace Vectora\Pinecone\Search;

use Vectora\Pinecone\DTO\QueryVectorMatch;

/** Slices match lists for offset/limit pagination (Phase 10). */
final class OffsetPagination
{
    /**
     * @param  list<QueryVectorMatch>  $matches
     * @return array{0: list<QueryVectorMatch>, 1: int} Sliced matches and total count before slice
     */
    public static function slice(array $matches, int $offset, int $limit): array
    {
        $total = count($matches);
        if ($offset < 0) {
            $offset = 0;
        }
        if ($limit < 1) {
            return [[], $total];
        }

        return [array_values(array_slice($matches, $offset, $limit)), $total];
    }
}
