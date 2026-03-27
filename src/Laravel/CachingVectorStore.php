<?php

declare(strict_types=1);

namespace Vectora\Pinecone\Laravel;

use Illuminate\Contracts\Cache\Repository;
use Vectora\Pinecone\Contracts\VectorStoreContract;
use Vectora\Pinecone\Core\Http\Json;
use Vectora\Pinecone\DTO\DeleteVectorsRequest;
use Vectora\Pinecone\DTO\DescribeIndexStatsResult;
use Vectora\Pinecone\DTO\QueryVectorsRequest;
use Vectora\Pinecone\DTO\QueryVectorsResult;
use Vectora\Pinecone\DTO\UpsertResult;
use Vectora\Pinecone\DTO\UpsertVectorsRequest;

/**
 * Decorates {@see VectorStoreContract} with Laravel cache for {@see query()} only.
 */
final class CachingVectorStore implements VectorStoreContract
{
    public function __construct(
        private readonly VectorStoreContract $inner,
        private readonly Repository $cache,
        private readonly string $keyPrefix,
        private readonly ?int $ttlSeconds,
        private readonly string $indexFingerprint,
    ) {}

    public function upsert(UpsertVectorsRequest $request): UpsertResult
    {
        return $this->inner->upsert($request);
    }

    public function query(QueryVectorsRequest $request): QueryVectorsResult
    {
        $key = $this->cacheKey($request);

        if ($this->ttlSeconds === null) {
            /** @var QueryVectorsResult */
            return $this->cache->rememberForever($key, fn (): QueryVectorsResult => $this->inner->query($request));
        }

        /** @var QueryVectorsResult */
        return $this->cache->remember($key, $this->ttlSeconds, fn (): QueryVectorsResult => $this->inner->query($request));
    }

    public function delete(DeleteVectorsRequest $request): void
    {
        $this->inner->delete($request);
    }

    public function describeIndexStats(): DescribeIndexStatsResult
    {
        return $this->inner->describeIndexStats();
    }

    private function cacheKey(QueryVectorsRequest $request): string
    {
        $payload = Json::encode($request->toApiBody());

        return $this->keyPrefix.':'.hash('sha256', $this->indexFingerprint.'|'.$payload);
    }
}
