<?php

declare(strict_types=1);

namespace Vectora\Pinecone\Tests\Feature\Eloquent;

use Illuminate\Support\Facades\Bus;
use Vectora\Pinecone\Laravel\Jobs\SyncModelEmbeddingJob;
use Vectora\Pinecone\Tests\Fixtures\EmbeddableArticle;

final class QueuedCreateDispatchesSyncModelEmbeddingJobTest extends EmbeddingsFeatureTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->mergePineconeConfig([
            'eloquent' => ['default_sync' => 'queued'],
        ]);
    }

    public function test_dispatches_sync_job_when_configured(): void
    {
        Bus::fake();

        EmbeddableArticle::create(['title' => 'A', 'body' => 'B']);

        Bus::assertDispatched(SyncModelEmbeddingJob::class);
    }
}
