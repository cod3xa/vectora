<?php

declare(strict_types=1);

namespace Vectora\Pinecone\Embeddings;

/** Deterministic pseudo-embeddings from text hash (tests / local dev without API keys). */
final class DeterministicEmbeddingDriver extends AbstractEmbeddingDriver
{
    public function __construct(
        private readonly int $dimensions = 8,
    ) {
        if ($dimensions < 1) {
            throw new \InvalidArgumentException('dimensions must be at least 1.');
        }
    }

    public function embed(string $text): array
    {
        if ($text === '') {
            throw new \InvalidArgumentException('Cannot embed empty text.');
        }

        $hash = hash('sha256', $text, true);
        $values = [];
        for ($i = 0; $i < $this->dimensions; $i++) {
            $b = ord($hash[$i % 32]);
            $values[] = ($b / 127.5) - 1.0;
        }

        return $values;
    }
}
