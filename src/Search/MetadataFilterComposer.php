<?php

declare(strict_types=1);

namespace Vectora\Pinecone\Search;

/**
 * Fluent helpers for Pinecone metadata filter DSL (Phase 10).
 */
final class MetadataFilterComposer
{
    /**
     * @param  array<string, mixed>  $must  Field => primitive or operator map
     * @return array<string, mixed>
     */
    public static function allOf(array $must): array
    {
        $clauses = [];
        foreach ($must as $k => $v) {
            $clauses[] = [$k => $v];
        }

        return ['$and' => $clauses];
    }

    /**
     * @param  list<array<string, mixed>>  $any
     * @return array<string, mixed>
     */
    public static function anyOf(array $any): array
    {
        return ['$or' => $any];
    }

    /**
     * @return array<string, mixed>
     */
    public static function eq(string $field, mixed $value): array
    {
        return [$field => ['$eq' => $value]];
    }

    /**
     * @param  list<mixed>  $values
     * @return array<string, mixed>
     */
    public static function in(string $field, array $values): array
    {
        return [$field => ['$in' => $values]];
    }

    /**
     * @return array<string, mixed>
     */
    public static function exists(string $field): array
    {
        return [$field => ['$exists' => true]];
    }
}
