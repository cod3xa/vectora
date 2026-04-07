<?php

declare(strict_types=1);

namespace Vectora\Pinecone\Tests\Feature\Laravel;

use Vectora\Pinecone\DTO\UpsertVectorsRequest;
use Vectora\Pinecone\DTO\VectorRecord;
use Vectora\Pinecone\Laravel\Facades\Pinecone;
use Vectora\Pinecone\Laravel\VectorStoreManager;

final class AdvancedSearchBuilderTest extends PineconeFeatureTestCase
{
    public function test_advanced_search_memory_store(): void
    {
        $this->mergePineconeConfig([
            'vector_store' => ['default' => 'memory'],
            'embeddings' => [
                'default' => 'deterministic',
                'drivers' => ['deterministic' => ['dimensions' => 8]],
            ],
        ]);

        $store = $this->app->make(VectorStoreManager::class)->driver();
        $store->upsert(new UpsertVectorsRequest([
            new VectorRecord('1', array_fill(0, 8, 0.0), ['text' => 'hello world foo']),
            new VectorRecord('2', array_fill(0, 8, 0.1), ['text' => 'other']),
        ], 'ns'));

        $r = Pinecone::advancedSearch()
            ->queryText('hello world')
            ->fetchTopK(5)
            ->namespace('ns')
            ->withKeywordBoost('text')
            ->normalizeMinMax()
            ->execute();

        $this->assertNotEmpty($r->matches);
    }
}
