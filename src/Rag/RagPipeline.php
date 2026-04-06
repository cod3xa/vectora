<?php

declare(strict_types=1);

namespace Vectora\Pinecone\Rag;

use Vectora\Pinecone\Contracts\ConversationMemory;
use Vectora\Pinecone\Contracts\LLMDriver;
use Vectora\Pinecone\Contracts\RagRetrieverContract;
use Vectora\Pinecone\DTO\RagAnswer;

/** Orchestrates retrieve → prompt → LLM (and optional streaming / memory). */
final class RagPipeline
{
    public function __construct(
        private readonly RagRetrieverContract $retriever,
        private readonly LLMDriver $llm,
        private readonly RagPromptBuilder $promptBuilder = new RagPromptBuilder,
        private readonly ?ConversationMemory $memory = null,
        private readonly string $systemInstructions = 'Answer using only the provided context when possible. If context is insufficient, say so briefly.',
    ) {}

    /**
     * @param  array<string, mixed>|null  $additionalFilter
     */
    public function ask(string $question, int $topK = 5, ?array $additionalFilter = null): RagAnswer
    {
        $sources = $this->retriever->retrieve($question, $topK, $additionalFilter);
        $prior = $this->memoryPriorForPrompt();
        $messages = $this->promptBuilder->buildMessages($sources, $question, $this->systemInstructions, $prior);

        $text = $this->llm->chat($messages);
        $this->memory?->addUser($question);
        $this->memory?->addAssistant($text);

        return new RagAnswer($text, $sources);
    }

    /**
     * Retrieves context first, then streams LLM output. Memory receives the full assistant reply after the stream ends.
     *
     * @param  array<string, mixed>|null  $additionalFilter
     * @return \Generator<int, string>
     */
    public function streamAsk(string $question, int $topK = 5, ?array $additionalFilter = null): \Generator
    {
        $sources = $this->retriever->retrieve($question, $topK, $additionalFilter);
        $prior = $this->memoryPriorForPrompt();
        $messages = $this->promptBuilder->buildMessages($sources, $question, $this->systemInstructions, $prior);

        $buffer = '';
        foreach ($this->llm->streamChat($messages) as $delta) {
            $buffer .= $delta;
            if ($delta !== '') {
                yield $delta;
            }
        }
        $this->memory?->addUser($question);
        $this->memory?->addAssistant($buffer);
    }

    /**
     * @return list<array{role: string, content: string}>
     */
    private function memoryPriorForPrompt(): array
    {
        if ($this->memory === null) {
            return [];
        }

        return $this->memory->messages();
    }
}
