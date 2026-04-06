<?php

declare(strict_types=1);

namespace Vectora\Pinecone\Tests\Feature\Laravel;

use Vectora\Pinecone\Laravel\Rag\RagQueryFactory;

final class RagQueryFactoryValidationTest extends PineconeFeatureTestCase
{
    public function test_using_rejects_non_model_class(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        $this->expectExceptionMessage('extend');
        $this->app->make(RagQueryFactory::class)->using(self::class);
    }
}
