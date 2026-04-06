<?php

declare(strict_types=1);

namespace Vectora\Pinecone\Tests\Unit\Ingestion;

use PHPUnit\Framework\TestCase;
use Vectora\Pinecone\Ingestion\Chunking\FixedSizeOverlappingChunker;

final class FixedSizeOverlappingChunkerTest extends TestCase
{
    public function test_splits_by_size(): void
    {
        $c = new FixedSizeOverlappingChunker(4, 0);
        $parts = $c->chunk('abcdefghij');
        $this->assertSame(['abcd', 'efgh', 'ij'], $parts);
    }

    public function test_overlap(): void
    {
        $c = new FixedSizeOverlappingChunker(4, 2);
        $parts = $c->chunk('abcdefgh');
        $this->assertGreaterThan(1, count($parts));
    }
}
