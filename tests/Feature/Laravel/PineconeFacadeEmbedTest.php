<?php

declare(strict_types=1);

namespace Vectora\Pinecone\Tests\Feature\Laravel;

use Vectora\Pinecone\Laravel\Facades\Pinecone;

final class PineconeFacadeEmbedTest extends PineconeFeatureTestCase
{
    public function test_facade_embed_delegates_to_default_driver(): void
    {
        $this->mergePineconeConfig([
            'embeddings' => [
                'default' => 'deterministic',
                'drivers' => [
                    'deterministic' => ['dimensions' => 3],
                ],
                'cache' => ['enabled' => false],
            ],
        ]);

        $this->assertCount(3, Pinecone::embed('facade'));
    }
}
