<?php

declare(strict_types=1);

namespace Vectora\Pinecone\Contracts;

use Vectora\Pinecone\DTO\CreateIndexRequest;
use Vectora\Pinecone\DTO\IndexDescriptionResult;

/** Control-plane index lifecycle (Pinecone-specific; swap implementation per provider). */
interface IndexAdminContract
{
    public function createIndex(CreateIndexRequest $request): void;

    public function deleteIndex(string $name): void;

    public function describeIndex(string $name): IndexDescriptionResult;
}
