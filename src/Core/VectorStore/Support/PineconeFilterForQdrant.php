<?php

declare(strict_types=1);

namespace Vectora\Pinecone\Core\VectorStore\Support;

/**
 * Converts a subset of Pinecone metadata filters to Qdrant filter JSON.
 *
 * Supports $and, $or, field $eq, and field $in.
 */
final class PineconeFilterForQdrant
{
    /**
     * @param  array<string, mixed>|null  $filter
     * @return array<string, mixed>|null
     */
    public static function convert(?array $filter): ?array
    {
        if ($filter === null || $filter === []) {
            return null;
        }

        return self::node($filter);
    }

    /**
     * @param  array<string, mixed>  $filter
     * @return array<string, mixed>
     */
    private static function node(array $filter): array
    {
        if (isset($filter['$and']) && is_array($filter['$and'])) {
            $must = [];
            foreach ($filter['$and'] as $sub) {
                if (is_array($sub)) {
                    $must[] = self::node($sub);
                }
            }

            return ['must' => $must];
        }

        if (isset($filter['$or']) && is_array($filter['$or'])) {
            $should = [];
            foreach ($filter['$or'] as $sub) {
                if (is_array($sub)) {
                    $should[] = self::node($sub);
                }
            }

            return ['should' => $should, 'minimum_should_match' => 1];
        }

        $must = [];
        foreach ($filter as $key => $cond) {
            if (! is_string($key) || str_starts_with($key, '$')) {
                continue;
            }
            if (! is_array($cond)) {
                continue;
            }
            if (array_key_exists('$eq', $cond)) {
                $must[] = [
                    'key' => $key,
                    'match' => ['value' => $cond['$eq']],
                ];
            }
            if (isset($cond['$in']) && is_array($cond['$in']) && $cond['$in'] !== []) {
                $shouldIn = [];
                foreach ($cond['$in'] as $val) {
                    $shouldIn[] = [
                        'key' => $key,
                        'match' => ['value' => $val],
                    ];
                }
                $must[] = [
                    'should' => $shouldIn,
                    'minimum_should_match' => 1,
                ];
            }
        }

        return ['must' => $must];
    }
}
