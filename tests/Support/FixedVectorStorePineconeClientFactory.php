<?php

declare(strict_types=1);

namespace Vectora\Pinecone\Tests\Support;

use Illuminate\Contracts\Foundation\Application;
use Vectora\Pinecone\Contracts\IndexAdminContract;
use Vectora\Pinecone\Contracts\VectorStoreContract;
use Vectora\Pinecone\Laravel\PineconeClientFactory;

/**
 * @internal Eloquent tests: always return a fixed vector store (and stub admin).
 */
final class FixedVectorStorePineconeClientFactory extends PineconeClientFactory
{
    public function __construct(
        Application $app,
        private readonly VectorStoreContract $fixedStore,
        private readonly IndexAdminContract $fixedAdmin,
    ) {
        parent::__construct($app);
    }

    public function vectorStore(?string $index = null): VectorStoreContract
    {
        return $this->fixedStore;
    }

    public function indexAdmin(): IndexAdminContract
    {
        return $this->fixedAdmin;
    }
}
