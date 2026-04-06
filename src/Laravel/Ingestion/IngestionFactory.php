<?php

declare(strict_types=1);

namespace Vectora\Pinecone\Laravel\Ingestion;

use Illuminate\Contracts\Foundation\Application;
use Vectora\Pinecone\Ingestion\ExtractorRegistry;
use Vectora\Pinecone\Ingestion\Http\GuzzleUrlReader;
use Vectora\Pinecone\Ingestion\IngestionPipeline;

final class IngestionFactory
{
    public function __construct(
        private readonly Application $app,
    ) {}

    public function builder(): IngestionBuilder
    {
        return new IngestionBuilder(
            $this->app,
            $this->app->make(ExtractorRegistry::class),
            $this->app->make(IngestionPipeline::class),
            $this->app->make(GuzzleUrlReader::class),
        );
    }
}
