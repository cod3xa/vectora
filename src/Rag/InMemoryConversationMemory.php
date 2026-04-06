<?php

declare(strict_types=1);

namespace Vectora\Pinecone\Rag;

use Vectora\Pinecone\Contracts\ConversationMemory;

/** In-process conversation buffer (suitable for a single HTTP request or demo). */
final class InMemoryConversationMemory implements ConversationMemory
{
    /** @var list<array{role: string, content: string}> */
    private array $turns = [];

    public function addUser(string $content): void
    {
        $this->turns[] = ['role' => 'user', 'content' => $content];
    }

    public function addAssistant(string $content): void
    {
        $this->turns[] = ['role' => 'assistant', 'content' => $content];
    }

    public function messages(): array
    {
        return $this->turns;
    }

    public function clear(): void
    {
        $this->turns = [];
    }
}
