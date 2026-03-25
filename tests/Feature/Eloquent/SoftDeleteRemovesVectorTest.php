<?php

declare(strict_types=1);

namespace Vectora\Pinecone\Tests\Feature\Eloquent;

use Vectora\Pinecone\Tests\Fixtures\EmbeddableArticle;

final class SoftDeleteRemovesVectorTest extends EmbeddingsFeatureTestCase
{
    public function test_soft_delete_removes_vector(): void
    {
        $article = EmbeddableArticle::create(['title' => 'A', 'body' => 'B']);
        $this->recordingStore->deleteRequests = [];

        $article->delete();

        $this->assertCount(1, $this->recordingStore->deleteRequests);
    }
}
