<?php

declare(strict_types=1);

namespace Vectora\Pinecone\LLM;

use Vectora\Pinecone\Contracts\LLMDriver;

/** Deterministic LLM for tests and offline demos; echoes last user message with optional prefix. */
final class StubLLMDriver implements LLMDriver
{
    public function __construct(
        private readonly string $prefix = 'STUB: ',
    ) {}

    public function chat(array $messages): string
    {
        $last = $this->lastUserContent($messages);

        return $this->prefix.$last;
    }

    public function streamChat(array $messages): \Generator
    {
        $text = $this->chat($messages);
        yield $text;
    }

    /**
     * @param  list<array{role: string, content: string}>  $messages
     */
    private function lastUserContent(array $messages): string
    {
        for ($i = count($messages) - 1; $i >= 0; $i--) {
            $m = $messages[$i];
            if (($m['role'] ?? '') === 'user') {
                return (string) ($m['content'] ?? '');
            }
        }

        return '';
    }
}
