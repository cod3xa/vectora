<?php

declare(strict_types=1);

namespace Vectora\Pinecone\Tests\Unit\Ingestion;

use PHPUnit\Framework\TestCase;
use Vectora\Pinecone\DTO\IngestedChunk;
use Vectora\Pinecone\Ingestion\Chunking\FixedSizeOverlappingChunker;
use Vectora\Pinecone\Ingestion\IngestionPipeline;

final class IngestionPipelineTest extends TestCase
{
    public function test_enrich_callback(): void
    {
        $pipe = new IngestionPipeline;
        $chunks = $pipe->run(
            'abcdefgh',
            new FixedSizeOverlappingChunker(4, 0),
            static function (IngestedChunk $c): IngestedChunk {
                return $c->withMetadata(['x' => 1]);
            },
            ['doc' => 't'],
        );
        $this->assertNotEmpty($chunks);
        $this->assertSame(1, $chunks[0]->metadata['x']);
        $this->assertSame('t', $chunks[0]->metadata['doc']);
    }
}
