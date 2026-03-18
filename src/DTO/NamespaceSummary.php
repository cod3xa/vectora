<?php

declare(strict_types=1);

namespace Vectora\Pinecone\DTO;

final readonly class NamespaceSummary
{
    public function __construct(
        public string $name,
        public int $vectorCount,
    ) {}
}
