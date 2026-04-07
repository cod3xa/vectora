<?php

declare(strict_types=1);

namespace Vectora\Pinecone\Tests\Unit\Ingestion;

use PHPUnit\Framework\TestCase;
use Vectora\Pinecone\Ingestion\Chunking\ParagraphChunker;

final class ParagraphChunkerTest extends TestCase
{
    public function test_splits_on_blank_lines(): void
    {
        $c = new ParagraphChunker(10, 5000);
        $parts = $c->chunk("First para.\n\nSecond para.\n\nThird.");
        $this->assertCount(1, $parts);
        $this->assertStringContainsString('First', $parts[0]);
    }
}
