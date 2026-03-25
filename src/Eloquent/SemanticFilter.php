<?php

declare(strict_types=1);

namespace Vectora\Pinecone\Eloquent;

/** Combines Pinecone metadata filters for {@see Concerns\HasEmbeddings::semanticSearch()}. */
final class SemanticFilter
{
    /**
     * @param  array<string, mixed>|null  $base
     * @param  array<string, mixed>|null  $extra
     * @return array<string, mixed>|null
     */
    public static function merge(?array $base, ?array $extra): ?array
    {
        if ($base === null || $base === []) {
            return $extra;
        }
        if ($extra === null || $extra === []) {
            return $base;
        }

        return ['$and' => [$base, $extra]];
    }
}
