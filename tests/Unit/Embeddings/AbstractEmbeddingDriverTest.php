<?php

declare(strict_types=1);

namespace Vectora\Pinecone\Tests\Unit\Embeddings;

use PHPUnit\Framework\TestCase;
use Vectora\Pinecone\Embeddings\AbstractEmbeddingDriver;

final class AbstractEmbeddingDriverTest extends TestCase
{
    public function test_default_embed_many_delegates_to_embed(): void
    {
        $d = new class extends AbstractEmbeddingDriver
        {
            public function embed(string $text): array
            {
                return [strlen($text)];
            }
        };

        $this->assertSame([[1], [2]], $d->embedMany(['a', 'ab']));
    }
}
