<?php

declare(strict_types=1);

namespace Vectora\Pinecone\Tests\Feature\Eloquent;

use Vectora\Pinecone\Tests\Fixtures\EmbeddableArticle;

final class SemanticSearchMergesAdditionalFilterTest extends EmbeddingsFeatureTestCase
{
    public function test_merges_additional_filter_with_and(): void
    {
        EmbeddableArticle::semanticSearch('q', 3, ['published' => true]);

        $filter = $this->recordingStore->lastQueryRequest?->filter;
        $this->assertIsArray($filter);
        $this->assertArrayHasKey('$and', $filter);
        $this->assertCount(2, $filter['$and']);
    }
}
