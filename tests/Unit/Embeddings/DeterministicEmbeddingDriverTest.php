<?php

declare(strict_types=1);

namespace Vectora\Pinecone\Tests\Unit\Embeddings;

use PHPUnit\Framework\TestCase;
use Vectora\Pinecone\Embeddings\DeterministicEmbeddingDriver;

final class DeterministicEmbeddingDriverTest extends TestCase
{
    public function test_dimensions_match_config(): void
    {
        $d = new DeterministicEmbeddingDriver(12);
        $v = $d->embed('hello');
        $this->assertCount(12, $v);
    }

    public function test_same_text_same_vector(): void
    {
        $d = new DeterministicEmbeddingDriver(8);
        $this->assertSame($d->embed('x'), $d->embed('x'));
    }

    public function test_different_text_different_vector(): void
    {
        $d = new DeterministicEmbeddingDriver(8);
        $this->assertNotSame($d->embed('a'), $d->embed('b'));
    }

    public function test_embed_many_preserves_order(): void
    {
        $d = new DeterministicEmbeddingDriver(4);
        $out = $d->embedMany(['a', 'b']);
        $this->assertSame($d->embed('a'), $out[0]);
        $this->assertSame($d->embed('b'), $out[1]);
    }

    public function test_empty_text_throws(): void
    {
        $d = new DeterministicEmbeddingDriver(4);
        $this->expectException(\InvalidArgumentException::class);
        $d->embed('');
    }

    public function test_invalid_dimensions_throws(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        new DeterministicEmbeddingDriver(0);
    }

    public function test_embed_many_rejects_non_string(): void
    {
        $d = new DeterministicEmbeddingDriver(4);
        $this->expectException(\InvalidArgumentException::class);
        /** @phpstan-ignore-next-line */
        $d->embedMany([1]);
    }
}
