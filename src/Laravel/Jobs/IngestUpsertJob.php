<?php

declare(strict_types=1);

namespace Vectora\Pinecone\Laravel\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Event;
use Throwable;
use Vectora\Pinecone\DTO\UpsertVectorsRequest;
use Vectora\Pinecone\DTO\VectorRecord;
use Vectora\Pinecone\Laravel\Events\VectorFailed;
use Vectora\Pinecone\Laravel\Events\VectorSynced;
use Vectora\Pinecone\Laravel\PineconeManager;
use Vectora\Pinecone\Laravel\VectorStoreManager;

/**
 * Queue-based ingestion: embed many texts then upsert (Phase 9).
 *
 * @param  list<array{text: string, metadata?: array<string, mixed>}>  $chunks
 */
final class IngestUpsertJob implements ShouldQueue
{
    use Dispatchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    /**
     * @param  list<array{text: string, metadata?: array<string, mixed>}>  $chunks
     */
    public function __construct(
        public array $chunks,
        public string $vectorIdPrefix,
        public ?string $index = null,
        public ?string $namespace = null,
    ) {
        $q = config('pinecone.queue', []);
        if (isset($q['connection']) && is_string($q['connection']) && $q['connection'] !== '') {
            $this->onConnection($q['connection']);
        }
        if (isset($q['queue']) && is_string($q['queue']) && $q['queue'] !== '') {
            $this->onQueue($q['queue']);
        }
    }

    public function handle(VectorStoreManager $stores, PineconeManager $mgr): void
    {
        if ($this->chunks === []) {
            return;
        }

        try {
            $texts = array_map(static fn (array $row): string => (string) $row['text'], $this->chunks);
            $vectors = $mgr->embedMany($texts);
            $records = [];
            foreach ($this->chunks as $i => $row) {
                $meta = isset($row['metadata']) && is_array($row['metadata']) ? $row['metadata'] : [];
                $records[] = new VectorRecord(
                    $this->vectorIdPrefix.'-'.$i,
                    $vectors[$i],
                    $meta !== [] ? $meta : null,
                );
            }
            $request = new UpsertVectorsRequest($records, $this->namespace);
            $stores->driver(null, $this->index)->upsert($request);

            Event::dispatch(new VectorSynced('ingest_upsert', [
                'count' => count($records),
                'index' => $this->index,
                'namespace' => $this->namespace,
            ]));
        } catch (Throwable $e) {
            Event::dispatch(new VectorFailed('ingest_upsert', $e->getMessage(), [
                'index' => $this->index,
                'namespace' => $this->namespace,
            ]));

            throw $e;
        }
    }
}
