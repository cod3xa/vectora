<?php

declare(strict_types=1);

namespace Vectora\Pinecone\Tests\Feature\Eloquent;

use Vectora\Pinecone\Tests\Fixtures\EmbeddableArticle;

final class RestoredModelUpsertsVectorTest extends EmbeddingsFeatureTestCase
{
    public function test_restored_model_reindexes(): void
    {
        $article = EmbeddableArticle::create(['title' => 'A', 'body' => 'B']);
        $article->delete();
        $this->recordingStore->upsertRequests = [];

        $article->restore();

        $this->assertCount(1, $this->recordingStore->upsertRequests);
    }
}
