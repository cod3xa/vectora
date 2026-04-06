<?php

declare(strict_types=1);

namespace Vectora\Pinecone\Tests\Feature\Laravel;

use Vectora\Pinecone\Laravel\Facades\Vector;
use Vectora\Pinecone\Tests\Feature\Eloquent\EmbeddingsFeatureTestCase;
use Vectora\Pinecone\Tests\Fixtures\EmbeddableArticle;

final class VectorFacadeRagTest extends EmbeddingsFeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->mergePineconeConfig([
            'vector_store' => ['default' => 'memory'],
            'llm' => [
                'default' => 'stub',
                'drivers' => [
                    'stub' => ['prefix' => 'AI: '],
                ],
            ],
        ]);
    }

    public function test_ask_returns_stub_answer_and_sources(): void
    {
        $article = EmbeddableArticle::query()->create([
            'title' => 'RAG Title',
            'body' => 'Unique body for retrieval.',
        ]);
        $article->syncVectorEmbeddingNow();

        $answer = Vector::using(EmbeddableArticle::class)->ask('Unique body');

        $this->assertStringContainsString('AI:', $answer->text);
        $this->assertNotSame([], $answer->sources);
    }
}
