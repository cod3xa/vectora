<?php

declare(strict_types=1);

namespace Vectora\Pinecone\Tests\Feature\Eloquent;

use Vectora\Pinecone\Tests\Fixtures\EmbeddableArticle;

final class ModelDeletedRemovesVectorTest extends EmbeddingsFeatureTestCase
{
    public function test_hard_delete_sends_delete_request(): void
    {
        $article = EmbeddableArticle::create(['title' => 'A', 'body' => 'B']);
        $id = (string) $article->getKey();
        $this->recordingStore->deleteRequests = [];

        $article->forceDelete();

        $this->assertCount(1, $this->recordingStore->deleteRequests);
        $this->assertSame([$id], $this->recordingStore->deleteRequests[0]->ids);
    }
}
