<?php

declare(strict_types=1);

namespace Vectora\Pinecone\Laravel\Rag;

use Illuminate\Database\Eloquent\Model;
use Vectora\Pinecone\Contracts\Embeddable;
use Vectora\Pinecone\Laravel\Embeddings\LLMManager;
use Vectora\Pinecone\Laravel\Ingestion\IngestionBuilder;
use Vectora\Pinecone\Laravel\Ingestion\IngestionFactory;

/** Factory for {@see RagQueryBuilder} and {@see IngestionBuilder} (`Vector` facade). */
final class RagQueryFactory
{
    public function __construct(
        private readonly LLMManager $llmManager,
        private readonly IngestionFactory $ingestionFactory,
    ) {}

    /**
     * @param  class-string  $modelClass
     */
    public function using(string $modelClass): RagQueryBuilder
    {
        if (! class_exists($modelClass)) {
            throw new \InvalidArgumentException(sprintf('Model class [%s] does not exist.', $modelClass));
        }
        if (! is_subclass_of($modelClass, Model::class)) {
            throw new \InvalidArgumentException(sprintf('[%s] must extend %s.', $modelClass, Model::class));
        }
        if (! is_subclass_of($modelClass, Embeddable::class)) {
            throw new \InvalidArgumentException(sprintf('[%s] must implement %s.', $modelClass, Embeddable::class));
        }

        return new RagQueryBuilder($modelClass, $this->llmManager);
    }

    public function ingest(): IngestionBuilder
    {
        return $this->ingestionFactory->builder();
    }
}
