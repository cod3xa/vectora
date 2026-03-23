<?php

declare(strict_types=1);

namespace Vectora\Pinecone\DTO;

final readonly class UpsertResult
{
    public function __construct(
        public int $upsertedCount,
    ) {}
}
