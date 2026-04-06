<?php

declare(strict_types=1);

namespace Vectora\Pinecone\Laravel\Rag;

use Illuminate\Database\Eloquent\Model;
use Vectora\Pinecone\Contracts\ConversationMemory;
use Vectora\Pinecone\Contracts\Embeddable;
use Vectora\Pinecone\DTO\RagAnswer;
use Vectora\Pinecone\Laravel\Embeddings\LLMManager;
use Vectora\Pinecone\Rag\RagPipeline;
use Vectora\Pinecone\Rag\RagPromptBuilder;

/**
 * Fluent entry for RAG on an {@see Embeddable} model class.
 */
final class RagQueryBuilder
{
    private int $topK = 5;

    /** @var array<string, mixed>|null */
    private ?array $filter = null;

    private ?ConversationMemory $memory = null;

    private ?string $llmDriver = null;

    private string $systemPrompt = 'Answer using only the provided context when possible. If context is insufficient, say so briefly.';

    /**
     * @param  class-string<Model&Embeddable>  $modelClass
     */
    public function __construct(
        private readonly string $modelClass,
        private readonly LLMManager $llmManager,
    ) {}

    public function topK(int $topK): self
    {
        if ($topK < 1) {
            throw new \InvalidArgumentException('topK must be at least 1.');
        }
        $clone = clone $this;
        $clone->topK = $topK;

        return $clone;
    }

    /**
     * @param  array<string, mixed>|null  $additionalFilter
     */
    public function filter(?array $additionalFilter): self
    {
        $clone = clone $this;
        $clone->filter = $additionalFilter;

        return $clone;
    }

    public function memory(?ConversationMemory $memory): self
    {
        $clone = clone $this;
        $clone->memory = $memory;

        return $clone;
    }

    public function systemPrompt(string $instructions): self
    {
        $clone = clone $this;
        $clone->systemPrompt = $instructions;

        return $clone;
    }

    public function llm(?string $driverName): self
    {
        $clone = clone $this;
        $clone->llmDriver = $driverName;

        return $clone;
    }

    public function ask(string $question): RagAnswer
    {
        $pipeline = $this->pipeline();

        return $pipeline->ask($question, $this->topK, $this->filter);
    }

    /**
     * @return \Generator<int, string>
     */
    public function streamAsk(string $question): \Generator
    {
        $pipeline = $this->pipeline();
        yield from $pipeline->streamAsk($question, $this->topK, $this->filter);
    }

    private function pipeline(): RagPipeline
    {
        $retriever = new EmbeddableRagRetriever($this->modelClass);
        $llm = $this->llmManager->driver($this->llmDriver);

        return new RagPipeline(
            $retriever,
            $llm,
            new RagPromptBuilder,
            $this->memory,
            $this->systemPrompt,
        );
    }
}
