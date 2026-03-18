<?php

declare(strict_types=1);

namespace Vectora\Pinecone\DTO;

/**
 * @param  list<VectorRecord>  $vectors
 */
final readonly class UpsertVectorsRequest
{
    /**
     * @param  list<VectorRecord>  $vectors
     */
    public function __construct(
        public array $vectors,
        public ?string $namespace = null,
    ) {
        if ($vectors === []) {
            throw new \InvalidArgumentException('Upsert requires at least one vector.');
        }
    }

    /**
     * @return array{vectors: list<array<string, mixed>>, namespace?: string}
     */
    public function toApiBody(): array
    {
        $body = [
            'vectors' => array_map(
                static fn (VectorRecord $v) => $v->toApiArray(),
                $this->vectors
            ),
        ];
        if ($this->namespace !== null && $this->namespace !== '') {
            $body['namespace'] = $this->namespace;
        }

        return $body;
    }
}
