<?php

declare(strict_types=1);

namespace Vectora\Pinecone\Tests\Feature\Eloquent;

use Vectora\Pinecone\Tests\Fixtures\EmbeddableArticle;

final class ShouldSyncFalseSkipsUpsertTest extends EmbeddingsFeatureTestCase
{
    public function test_skips_vector_when_should_sync_returns_false(): void
    {
        UnsyncedEmbeddableArticle::create(['title' => 'A', 'body' => 'B']);

        $this->assertCount(0, $this->recordingStore->upsertRequests);
    }
}

final class UnsyncedEmbeddableArticle extends EmbeddableArticle
{
    public function shouldSyncVectorEmbedding(): bool
    {
        return false;
    }
}
