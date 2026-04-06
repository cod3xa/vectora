<?php

declare(strict_types=1);

namespace Vectora\Pinecone\Tests\Feature\Eloquent;

use Vectora\Pinecone\Tests\Fixtures\EmbeddableArticle;

final class HasEmbeddingsRagBuilderTest extends EmbeddingsFeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->mergePineconeConfig([
            'vector_store' => ['default' => 'memory'],
            'llm' => ['default' => 'stub'],
        ]);
    }

    public function test_rag_ask_matches_static_entrypoint(): void
    {
        $article = EmbeddableArticle::query()->create([
            'title' => 'Doc',
            'body' => 'Chunk content here.',
        ]);
        $article->syncVectorEmbeddingNow();

        $a = EmbeddableArticle::rag()->topK(3)->ask('Chunk content');
        $this->assertNotSame('', $a->text);
        $this->assertGreaterThanOrEqual(1, count($a->sources));
    }
}
