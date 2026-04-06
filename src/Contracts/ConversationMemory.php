<?php

declare(strict_types=1);

namespace Vectora\Pinecone\Contracts;

use Vectora\Pinecone\Rag\RagPipeline;

/**
 * Optional multi-turn context for {@see RagPipeline}.
 *
 * @phpstan-type MemoryMessage array{role: string, content: string}
 */
interface ConversationMemory
{
    public function addUser(string $content): void;

    public function addAssistant(string $content): void;

    /**
     * Prior turns (not including the latest user message injected by the pipeline).
     *
     * @return list<MemoryMessage>
     */
    public function messages(): array;

    public function clear(): void;
}
