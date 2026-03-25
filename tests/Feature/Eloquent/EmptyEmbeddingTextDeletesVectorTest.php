<?php

declare(strict_types=1);

namespace Vectora\Pinecone\Tests\Feature\Eloquent;

use Vectora\Pinecone\Tests\Fixtures\EmbeddableArticle;

final class EmptyEmbeddingTextDeletesVectorTest extends EmbeddingsFeatureTestCase
{
    public function test_empty_text_triggers_delete_not_upsert(): void
    {
        EmbeddableArticle::create(['title' => '', 'body' => '']);

        $this->assertCount(0, $this->recordingStore->upsertRequests);
        $this->assertCount(1, $this->recordingStore->deleteRequests);
    }
}
